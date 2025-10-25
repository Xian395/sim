<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->enum('type', ['in', 'out']);
            $table->integer('quantity');
            $table->decimal('acquisition_price', 10, 2)->nullable();
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->integer('remaining_quantity')->nullable();
            $table->json('batch_allocations')->nullable();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};