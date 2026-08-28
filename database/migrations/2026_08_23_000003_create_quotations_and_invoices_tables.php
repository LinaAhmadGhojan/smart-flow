<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('date');
            $table->string('client_name');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('status')->default('draft'); // draft, sent, accepted, cancelled
            $table->text('comments')->nullable();
            $table->string('currency', 10)->default('AED');
            $table->decimal('tax_percent', 8, 2)->default(0);
            $table->decimal('withholding_tax_percent', 8, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('withholding_tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->text('description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->date('date');
            $table->string('client_name');
            $table->string('status')->default('draft'); // draft, sent, paid, cancelled
            $table->text('notes')->nullable();
            $table->string('currency', 10)->default('AED');
            // Partial amount from quotation total
            $table->decimal('amount', 12, 2);
            $table->decimal('percent', 8, 2)->nullable(); // e.g. 50 for 50%
            $table->decimal('tax_percent', 8, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
