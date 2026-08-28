<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_slots', function (Blueprint $table) {
            $table->string('engineer_name')->nullable()->after('customer_email');
            $table->string('engineer_phone')->nullable()->after('engineer_name');
            $table->enum('type', ['visit', 'maintenance'])->default('visit')->after('engineer_phone');
            $table->string('location')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_slots', function (Blueprint $table) {
            $table->dropColumn(['engineer_name', 'engineer_phone', 'type', 'location']);
        });
    }
};
