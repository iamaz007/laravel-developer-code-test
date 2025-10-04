<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranslationKeyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'key'         => $this->key,
            'description' => $this->description,
            'tags'        => $this->whenLoaded('tags', fn() => $this->tags->pluck('name')),
            'values'      => $this->whenLoaded('values', function () {
                // return as [{locale:'en', value:'...'}, ...]
                return $this->values->map(fn($v) => [
                    'locale'  => $v->locale_code,
                    'value'   => $v->value,
                    'version' => $v->version,
                    'updated' => $v->updated_at,
                ])->values();
            }),
            'updated_at'  => $this->updated_at,
        ];
    }
}
