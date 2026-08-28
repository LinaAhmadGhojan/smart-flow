<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->nullOnDelete();
            $table->string('location')->nullable()->after('description_ar');
            $table->string('maps_url')->nullable()->after('location');
            $table->string('status')->default('in_progress')->after('maps_url');
            $table->boolean('is_public')->default(false)->after('status');
            $table->string('qr_path')->nullable()->after('is_public');
            $table->timestamp('completed_at')->nullable()->after('qr_path');
        });

        Schema::create('project_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('label');
            $table->string('path');
            $table->string('visibility')->default('private'); // public | private
            $table->string('kind')->default('document'); // image | video | document
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('customer_id')->constrained('projects')->nullOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('quotation_id')->constrained('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::dropIfExists('project_files');
        Schema::dropIfExists('project_contacts');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropColumn(['location', 'maps_url', 'status', 'is_public', 'qr_path', 'completed_at']);
        });
    }
};
