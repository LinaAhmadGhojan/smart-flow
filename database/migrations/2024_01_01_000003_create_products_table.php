<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('brand')->nullable();
            $table->string('price');
            $table->decimal('price_number', 10, 2)->nullable();
            $table->string('image')->nullable();
            $table->boolean('in_stock')->default(true);
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->json('features')->nullable();
            $table->text('whatsapp_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
