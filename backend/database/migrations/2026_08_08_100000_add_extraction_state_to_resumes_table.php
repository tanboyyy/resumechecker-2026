<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resumes', function (Blueprint $table) {
            $table->string('extraction_status')->default('pending')->after('text_extracted');
            $table->text('extraction_error')->nullable()->after('extraction_status');
            $table->index('extraction_status');
        });

        // Existing rows already carry their outcome in text_extracted.
        DB::table('resumes')->where('text_extracted', true)->update(['extraction_status' => 'completed']);
    }

    public function down(): void
    {
        Schema::table('resumes', function (Blueprint $table) {
            $table->dropIndex(['extraction_status']);
            $table->dropColumn(['extraction_status', 'extraction_error']);
        });
    }
};
