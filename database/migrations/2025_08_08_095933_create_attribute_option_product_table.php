<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for the attribute_option_product pivot table.
 *
 * This table links attribute options to products and allows for a price addition (in fils).
 * 1 Kuwaiti Dinar (KD) = 1000 fils.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attribute_option_product', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attribute_option_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            /**
             * Price addition in fils (1 KD = 1000 fils).
             */
            $table->unsignedBigInteger('price_addition')->comment('In fils (1 KD = 1000 fils)');

            $table->timestamps();

            $table->unique(['attribute_option_id', 'product_id']);
            $table->index('product_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_option_product');
    }
};
