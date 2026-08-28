<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_payments', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('project_id')->constrained('invoices')->nullOnDelete();
        });

        Schema::table('project_payments', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        Schema::table('project_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->change();
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_payments', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });

        Schema::table('project_payments', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        Schema::table('project_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable(false)->change();
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
        });
    }
};
