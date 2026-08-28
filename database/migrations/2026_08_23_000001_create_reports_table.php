<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_slot_id')->nullable()->constrained('appointment_slots')->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->json('images')->nullable();
            $table->string('client_name')->nullable();
            $table->string('engineer_name')->nullable();
            $table->date('visit_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
