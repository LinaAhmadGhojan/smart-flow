<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'group_id')) {
                $table->foreignId('group_id')
                    ->nullable()
                    ->after('category_id')
                    ->constrained('product_groups')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'group_id')) {
                $table->dropConstrainedForeignId('group_id');
            }
        });

        Schema::dropIfExists('product_groups');
    }
};
