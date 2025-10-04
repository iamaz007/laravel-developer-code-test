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
        Schema::table('translation_values', function (Blueprint $table) {
            // speeds: WHERE locale_code = ? AND JOIN on translation_key_id
            $table->index(['locale_code', 'translation_key_id'], 'idx_tv_locale_key');
        });

        Schema::table('tags', function (Blueprint $table) {
            // speeds: WHERE t.name IN (...)
            $table->index('name', 'idx_tags_name');
        });

        // Optional: if you frequently filter keys by updated_at in lists
        Schema::table('translation_keys', function (Blueprint $table) {
            $table->index('updated_at', 'idx_tk_updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('translation_values', function (Blueprint $table) {
            $table->dropIndex('idx_tv_locale_key');
        });
        Schema::table('tags', function (Blueprint $table) {
            $table->dropIndex('idx_tags_name');
        });
        Schema::table('translation_keys', function (Blueprint $table) {
            $table->dropIndex('idx_tk_updated_at');
        });
    }
};
