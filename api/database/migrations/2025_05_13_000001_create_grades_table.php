<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('module_item_id')
                  ->constrained('module_items')
                  ->cascadeOnDelete();
            $table->foreignId('submission_id')
                  ->nullable()
                  ->constrained('submissions')
                  ->nullOnDelete();
            $table->foreignId('graded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->float('score');
            $table->string('letter_grade')->nullable();
            $table->text('feedback')->nullable();
            $table->json('rubric_scores')->nullable(); // For storing detailed rubric scores
            $table->timestamp('graded_at')->nullable();
            $table->boolean('is_final')->default(true); // To mark if this is the final grade
            $table->timestamps();
            $table->softDeletes();

            // Add unique constraint to prevent multiple final grades
            $table->unique(['user_id', 'module_item_id', 'is_final'], 'unique_final_grade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
}; 