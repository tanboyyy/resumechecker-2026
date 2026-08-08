<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('severity');
            $table->text('message');
            $table->text('suggestion')->nullable();
            $table->string('section')->nullable();
            $table->timestamps();

            $table->index('analysis_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_feedback');
    }
};
