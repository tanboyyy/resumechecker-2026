<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // general, comparison, rewrite, interview
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->unsignedSmallInteger('ats_score')->nullable();
            $table->json('raw_response')->nullable();
            $table->text('job_description')->nullable();
            $table->text('prompt_used')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['resume_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
