<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->boolean('is_section')->default(false)->after('product_id');
        });

        Schema::create('project_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_type', 40)->default('cash'); // cash, bank, card, transfer, cheque, other
            $table->date('paid_at');
            $table->string('receipt_path')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('project_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('amount', 12, 2);
            $table->date('spent_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('project_delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('number')->nullable();
            $table->date('delivered_at');
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->json('items')->nullable(); // [{description, quantity}]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_delivery_notes');
        Schema::dropIfExists('project_expenses');
        Schema::dropIfExists('project_payments');

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('is_section');
        });
    }
};
