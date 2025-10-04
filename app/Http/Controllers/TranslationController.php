<?php

namespace App\Http\Controllers;

use App\Http\Resources\TranslationKeyResource;
use App\Models\Tag;
use App\Models\TranslationKey;
use App\Models\TranslationValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $r)
    {
        $r->validate([
            'tag'     => 'sometimes|string|max:50',
            'key'     => 'sometimes|string|max:255',
            'content' => 'sometimes|string|max:255',
            'locale'  => 'sometimes|string|max:10',
        ]);

        $q = TranslationKey::query()
            ->with(['tags'])
            ->with(['values' => function ($v) use ($r) {
                if ($r->filled('locale')) {
                    $v->where('locale_code', $r->input('locale'));
                }
            }]);

        if ($r->filled('tag')) {
            $q->withTag($r->tag);
        }
        if ($r->filled('key')) {
            $q->keyLike($r->key);
        }
        if ($r->filled('content')) {
            $q->contentLike($r->input('content'));
        }

        return TranslationKeyResource::collection(
            $q->orderByDesc('updated_at')->paginate(25)
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r)
    {
        return DB::transaction(function () use ($r) {
            // create or find the key
            $key = TranslationKey::firstOrCreate(
                ['key' => $r->input('key')],
                ['description' => $r->input('description')]
            );

            $value = TranslationValue::updateOrCreate(
                ['translation_key_id' => $key->id, 'locale_code' => $r->input('locale')],
                [
                    'value'   => $r->input('value'),
                    // bump version if record exists
                    'version' => DB::raw('version + 1')
                ]
            );

            // attach tags if provided
            if ($r->filled('tags')) {
                $tagIds = collect($r->input('tags'))
                    ->map(fn($name) => Tag::firstOrCreate(['name' => $name])->id)
                    ->all();
                $key->tags()->syncWithoutDetaching($tagIds);
            }

            // reload relations for response
            $key->load(['tags', 'values' => function ($v) use ($r) {
                $v->where('locale_code', $r->input('locale'));
            }]);

            return (new TranslationKeyResource($key))->additional(['created' => true]);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $key = TranslationKey::with(['values', 'tags'])->findOrFail($id);
        return new TranslationKeyResource($key);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $r, string $id)
    {
        return DB::transaction(function () use ($r, $id) {
            $key = TranslationKey::findOrFail($id);

            if ($r->has('description')) {
                $key->description = $r->input('description');
                $key->save();
            }

            if ($r->filled('value')) {
                // require locale when changing value
                $locale = $r->input('locale');
                TranslationValue::updateOrCreate(
                    ['translation_key_id' => $key->id, 'locale_code' => $locale],
                    [
                        'value'   => $r->input('value'),
                        'version' => DB::raw('version + 1')
                    ]
                );
            }

            if ($r->filled('tags')) {
                $tagIds = collect($r->tags)
                    ->map(fn($name) => Tag::firstOrCreate(['name' => $name])->id)
                    ->all();
                $key->tags()->sync($tagIds);
            }

            $key->load(['values', 'tags']);
            return new TranslationKeyResource($key);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
