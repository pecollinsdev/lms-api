<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_item_id')
                  ->constrained('module_items')
                  ->cascadeOnDelete();
            $table->enum('type', ['multiple_choice','text']);
            $table->text('prompt');
            $table->integer('order')->default(0);
            $table->decimal('points', 8, 2)->default(1);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
