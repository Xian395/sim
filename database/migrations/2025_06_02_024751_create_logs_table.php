<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); 
            $table->text('details')->nullable(); 
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->decimal('amount', 10, 2)->nullable();
            $table->json('metadata')->nullable();
            
          
            $table->index(['action', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};