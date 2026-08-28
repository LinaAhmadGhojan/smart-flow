<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'icon')) {
                $table->string('icon', 32)->nullable()->after('name_ar');
            }
            if (!Schema::hasColumn('categories', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'icon')) {
                $table->dropColumn('icon');
            }
            if (Schema::hasColumn('categories', 'description_ar')) {
                $table->dropColumn('description_ar');
            }
        });
    }
};
