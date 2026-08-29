<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')
                ->constrained('pos_product_categories')->nullOnDelete();
            $table->text('description')->nullable()->after('name');
            $table->string('image')->nullable()->after('barcode');
            $table->string('unit')->default('pcs')->after('tax_rate');
            // Warehouse/stockroom location, so a shortage can be traced to
            // exactly where the product is meant to be physically stocked.
            $table->string('rack')->nullable()->after('low_stock_threshold');
            $table->string('row')->nullable()->after('rack');
            $table->string('position')->nullable()->after('row');
        });
    }

    public function down(): void
    {
        Schema::table('pos_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['description', 'image', 'unit', 'rack', 'row', 'position']);
        });
    }
};
