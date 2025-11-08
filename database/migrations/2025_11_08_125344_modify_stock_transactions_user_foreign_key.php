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
        Schema::table('stock_transactions', function (Blueprint $table) {
            // Drop the existing restrict constraint
            $table->dropForeign(['user_id']);

            // Modify the column to be nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Add new constraint with set null on delete
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            // Drop the new constraint
            $table->dropForeign(['user_id']);

            // Revert to non-nullable
            $table->unsignedBigInteger('user_id')->change();

            // Add back the original restrict constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }
};
