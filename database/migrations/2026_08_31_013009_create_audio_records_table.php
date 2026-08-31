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
        Schema::create('audio_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorder_id')->constrained('users')->cascadeOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('mime')->nullable();
            $table->unsignedInteger('size');
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('status')->default('uploaded');
            $table->longText('transcript_text')->nullable();
            $table->string('transcription_provider')->nullable();
            $table->unsignedInteger('transcription_ms')->nullable();
            $table->text('transcription_error')->nullable();
            $table->timestamps();

            $table->index(['service_order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_records');
    }
};
