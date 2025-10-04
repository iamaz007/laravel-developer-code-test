<?php

namespace App\Http\Controllers;

use App\Models\TranslationKey;
use App\Models\TranslationValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'locale' => 'required|string|max:10',
            'tags'   => 'sometimes|array',
            'tags.*' => 'string|max:50',
        ]);

        $locale = $data['locale'];
        $tags   = $data['tags'] ?? [];

        // Single query:
        // translation_values (filtered by locale)
        //   JOIN translation_keys (to get the key string)
        //   optionally EXISTS in key_tag/tags for tag filtering
        $rows = DB::table('translation_values as tv')
            ->join('translation_keys as tk', 'tk.id', '=', 'tv.translation_key_id')
            ->when(!empty($tags), function ($q) use ($tags) {
                $q->whereExists(function ($sub) use ($tags) {
                    $sub->select(DB::raw(1))
                        ->from('key_tag as kt')
                        ->join('tags as t', 't.id', '=', 'kt.tag_id')
                        ->whereColumn('kt.translation_key_id', 'tk.id')
                        ->whereIn('t.name', $tags);
                });
            })
            ->where('tv.locale_code', $locale)
            ->select('tk.key', 'tv.value')
            ->get();

        // Build flat map key => value
        $result = [];
        foreach ($rows as $r) {
            $result[$r->key] = $r->value;
        }

        // ETag for client/CDN revalidation
        $etag = sha1(json_encode($result));
        if ($request->getETags() && trim($request->getETags()[0], '"') === $etag) {
            return response()->noContent(304);
        }

        return response()->json($result, 200, [
            'ETag' => "\"{$etag}\"",
            'Cache-Control' => 'no-store',
        ]);
    }
}
