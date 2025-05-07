<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('due_date');
            $table->decimal('max_score', 8, 2)->default(100);
            $table->enum('submission_type', ['file','essay','quiz'])
                  ->default('file');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
