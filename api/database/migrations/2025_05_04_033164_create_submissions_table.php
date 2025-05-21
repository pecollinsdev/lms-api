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
            $table->foreignId('module_item_id')
                  ->constrained('module_items')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->enum('submission_type', ['file','essay','quiz']);
            $table->string('file_path')->nullable();
            $table->text('content')->nullable();
            $table->json('answers')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->enum('status', ['pending','graded','late'])
                  ->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
