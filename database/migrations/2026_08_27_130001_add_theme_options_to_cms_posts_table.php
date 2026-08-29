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
        Schema::table('cms_posts', function (Blueprint $table): void {
            $table->string('container_width')->default('boxed')->after('template'); // boxed, full_width
            $table->boolean('show_title')->default(true)->after('container_width');
            $table->text('custom_css')->nullable()->after('show_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_posts', function (Blueprint $table): void {
            $table->dropColumn(['container_width', 'show_title', 'custom_css']);
        });
    }
};
