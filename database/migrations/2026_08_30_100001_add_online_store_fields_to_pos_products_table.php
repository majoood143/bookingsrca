<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')
                ->constrained('pos_brands')->nullOnDelete();
            $table->string('slug')->nullable()->unique()->after('name_ar');
            $table->text('description_ar')->nullable()->after('description');
            // Additional gallery images for the online product detail page, on top
            // of the existing single `image` used by the counter/receipt views.
            $table->json('gallery_images')->nullable()->after('image');
            // Separate from `is_active` (counter sellability): a product can be
            // sold at the counter without being exposed on the public storefront.
            $table->boolean('is_published_online')->default(false)->after('is_active');
            $table->unsignedInteger('views_count')->default(0)->after('is_published_online');
        });
    }

    public function down(): void
    {
        Schema::table('pos_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
            $table->dropColumn(['slug', 'description_ar', 'gallery_images', 'is_published_online', 'views_count']);
        });
    }
};
