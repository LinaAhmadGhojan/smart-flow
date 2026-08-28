<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_delivery_notes', function (Blueprint $table) {
            $table->string('received_by')->nullable()->after('notes');
            $table->string('delivered_by')->nullable()->after('received_by');
        });
    }

    public function down(): void
    {
        Schema::table('project_delivery_notes', function (Blueprint $table) {
            $table->dropColumn(['received_by', 'delivered_by']);
        });
    }
};
