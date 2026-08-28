<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->enum('status', ['available', 'booked'])->default('available');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['date', 'start_time']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_slots');
    }
};
