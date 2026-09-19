<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-editable translations that take precedence over the static lang files.
     *
     * `group` is the lang file name (e.g. "home") or "*" for the JSON files;
     * `key` is the dotted key inside that file (or the phrase itself for JSON).
     */
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('group');
            $table->string('key');
            $table->json('text');
            $table->timestamps();

            $table->unique(['group', 'key']);
        });

        // Seed the home page hero so it is editable straight away.
        $now = now();

        foreach (['heading', 'subheading'] as $field) {
            DB::table('translations')->insert([
                'group' => 'home',
                'key' => "hero.{$field}",
                'text' => json_encode([
                    'en' => (require lang_path('en/home.php'))['hero'][$field],
                    'ar' => (require lang_path('ar/home.php'))['hero'][$field],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
