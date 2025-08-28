<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('item_code', 20)->unique();
            $table->string('barcode')->unique();
            $table->decimal('price', 8, 2);
            $table->text('description')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['product_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('products');
    }
};