<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\TranslationKey;
use App\Models\TranslationValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LargeDatasetSeeder extends Seeder
{
    private int $totalKeys = 100000;   // start with 10k; you can raise to 50k later
    private int $chunkSize = 1000;    // insert in chunks

    private array $locales = ['en', 'fr', 'es'];
    private array $baseTags = ['web', 'mobile', 'desktop'];

    public function run(): void
    {
        $tagIds = [];
        foreach ($this->baseTags as $name) {
            $tagIds[$name] = Tag::firstOrCreate(['name' => $name])->id;
        }

        $now = now();

        $insertedKeyIds = [];
        for ($offset = 0; $offset < $this->totalKeys; $offset += $this->chunkSize) {
            $batch = [];

            for ($i = 0; $i < $this->chunkSize && ($offset + $i) < $this->totalKeys; $i++) {
                // create key strings like "app.label.<uuid>"
                $keyStr = 'app.label.' . Str::uuid()->toString();
                $batch[] = [
                    'key' => $keyStr,
                    'description' => 'Auto seed ' . Str::random(6),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            TranslationKey::insert($batch);

            $newKeys = TranslationKey::query()
                ->orderBy('id', 'desc')
                ->limit(count($batch))
                ->pluck('id', 'key');

            $insertedKeyIds = array_values($newKeys->toArray());

            $values = [];
            foreach ($insertedKeyIds as $kId) {
                $localesForKey = $this->pickLocales();
                foreach ($localesForKey as $loc) {
                    $values[] = [
                        'translation_key_id' => $kId,
                        'locale_code' => $loc,
                        'value' => 'Seeded value ' . Str::random(5),
                        'version' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            TranslationValue::insert($values);

            $pivot = [];
            foreach ($insertedKeyIds as $kId) {
                $pick = $this->pickTags($tagIds);
                foreach ($pick as $tId) {
                    $pivot[] = ['translation_key_id' => $kId, 'tag_id' => $tId];
                }
            }
            // Use insertIgnore to avoid dup primary collisions if re-run
            $this->insertIgnore('key_tag', $pivot);
        }
    }

    private function pickLocales(): array
    {
        $copy = $this->locales;
        shuffle($copy);
        return array_slice($copy, 0, rand(2, 3));
    }

    private function pickTags(array $tagIds): array
    {
        $ids = array_values($tagIds);
        shuffle($ids);
        return array_slice($ids, 0, rand(1, 2));
    }

    private function insertIgnore(string $table, array $rows): void
    {
        if (empty($rows)) return;

        $columns = array_keys($rows[0]);
        $bindings = [];
        $valuesSql = [];

        foreach ($rows as $row) {
            $placeholders = [];
            foreach ($columns as $col) {
                $bindings[] = $row[$col];
                $placeholders[] = '?';
            }
            $valuesSql[] = '(' . implode(',', $placeholders) . ')';
        }

        $sql = 'INSERT IGNORE INTO `' . $table . '` (`' . implode('`,`', $columns) . '`) VALUES ' . implode(',', $valuesSql);
        DB::statement($sql, $bindings);
    }
}
