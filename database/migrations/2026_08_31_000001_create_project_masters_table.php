<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_masters', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('location')->nullable();
            $table->string('maps_url')->nullable();
            $table->enum('media_type', ['image', 'video', 'text'])->default('image');
            $table->string('media_url')->nullable();
            $table->json('images')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('project_master_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_master_id')->constrained('project_masters')->cascadeOnDelete();
            $table->string('label');
            $table->string('path');
            $table->string('kind')->default('image'); // image | video | document
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_master_files');
        Schema::dropIfExists('project_masters');
    }
};
