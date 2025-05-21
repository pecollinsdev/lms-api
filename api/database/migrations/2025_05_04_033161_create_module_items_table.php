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
        Schema::create('module_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->string('type'); // video, assignment, quiz, document
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('order')->default(0);
            
            // Content data (JSON field to store type-specific content)
            $table->json('content_data')->nullable()->comment('Stores type-specific content like video URLs, document details, etc.');
            
            // Settings (JSON field for type-specific settings)
            $table->json('settings')->nullable()->comment('Stores type-specific settings like max attempts, passing score, etc.');
            
            // Common fields for assignments and quizzes
            $table->decimal('max_score', 8, 2)->nullable();
            $table->enum('submission_type', ['file', 'essay', 'quiz'])->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_items');
    }
};
