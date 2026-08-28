<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gate_machine_study_requests', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('site_location');
            $table->string('door_weight')->nullable();
            $table->string('door_width')->nullable();
            $table->string('door_height')->nullable();
            $table->string('door_material')->nullable();
            $table->enum('has_electrical_point', ['yes', 'no', 'unknown'])->default('unknown');
            $table->enum('has_machine_wiring', ['yes', 'no', 'unknown'])->default('unknown');
            $table->text('notes')->nullable();
            $table->enum('status', ['new', 'contacted', 'done'])->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gate_machine_study_requests');
    }
};
