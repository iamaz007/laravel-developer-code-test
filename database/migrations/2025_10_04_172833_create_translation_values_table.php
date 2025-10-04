<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('translation_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_key_id')->constrained()->cascadeOnDelete();
            $table->string('locale_code', 10);      // FK to locales.code (string FK)
            $table->text('value');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->unique(['translation_key_id', 'locale_code']);
            $table->index(['locale_code']);
            // $table->index(['updated_at']);

            // Optional later (MySQL): Fulltext for faster content search.
            // We’ll keep it for a later step so migrate runs everywhere.
        });

        Schema::create('key_tag', function (Blueprint $table) {
            $table->foreignId('translation_key_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['translation_key_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_values');
         Schema::dropIfExists('key_tag');
    }
};
