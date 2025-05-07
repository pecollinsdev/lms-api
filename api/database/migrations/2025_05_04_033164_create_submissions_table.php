<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')
                  ->constrained('assignments')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->enum('submission_type', ['file','essay','quiz']);
            $table->string('file_path')->nullable();
            $table->text('content')->nullable();
            $table->json('answers')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->decimal('grade', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->enum('status', ['pending','graded','late'])
                  ->default('pending');
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
