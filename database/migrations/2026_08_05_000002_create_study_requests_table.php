<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_requests', function (Blueprint $table) {
            $table->id();
            $table->enum('house_status', ['under_construction', 'existing']);
            $table->json('systems')->nullable();
            $table->string('systems_other')->nullable();
            $table->json('plans')->nullable();
            $table->json('plan_files')->nullable();
            $table->enum('infrastructure_by', ['contractor', 'company'])->nullable();
            $table->enum('proposed_system', ['wired', 'wireless'])->nullable();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('project_location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('appointment_slot_id')->nullable()->constrained('appointment_slots')->nullOnDelete();
            $table->enum('status', ['new', 'contacted', 'done'])->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_requests');
    }
};
