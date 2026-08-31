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
        Schema::create('service_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('audio_record_id')->nullable()->constrained()->nullOnDelete();
            $table->time('arrival_time')->nullable();
            $table->string('equipment_type')->nullable();
            $table->text('problem')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('work_done')->nullable();
            $table->text('tests_performed')->nullable();
            $table->text('result')->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->string('currency', 3)->default('MXN');
            $table->string('status')->default('draft');
            $table->json('llm_raw_json')->nullable();
            $table->decimal('llm_confidence', 3, 2)->nullable();
            $table->timestamps();

            $table->unique('service_order_id');
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_reports');
    }
};
