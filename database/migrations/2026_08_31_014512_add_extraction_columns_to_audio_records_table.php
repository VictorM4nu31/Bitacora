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
        Schema::table('audio_records', function (Blueprint $table) {
            $table->json('extracted_data')->nullable()->after('transcription_error');
            $table->string('analysis_provider')->nullable()->after('extracted_data');
            $table->unsignedInteger('analysis_ms')->nullable()->after('analysis_provider');
            $table->text('analysis_error')->nullable()->after('analysis_ms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audio_records', function (Blueprint $table) {
            $table->dropColumn(['extracted_data', 'analysis_provider', 'analysis_ms', 'analysis_error']);
        });
    }
};
