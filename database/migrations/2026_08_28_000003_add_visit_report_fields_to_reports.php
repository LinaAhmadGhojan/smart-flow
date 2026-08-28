<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('visit_time')->nullable()->after('visit_date');
            $table->string('visit_type')->nullable()->after('visit_time');
            $table->string('recipient_entity')->nullable()->after('visit_type');
            $table->text('site_address')->nullable()->after('recipient_entity');
            $table->string('site_company')->nullable()->after('site_address');
            $table->string('contact_phone')->nullable()->after('site_company');
            $table->string('delivery_method')->nullable()->after('contact_phone');
            $table->text('delivery_notes')->nullable()->after('delivery_method');
            $table->text('executed_works')->nullable()->after('delivery_notes');
            $table->text('report_notes')->nullable()->after('executed_works');
            $table->text('recommendations')->nullable()->after('report_notes');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn([
                'visit_time',
                'visit_type',
                'recipient_entity',
                'site_address',
                'site_company',
                'contact_phone',
                'delivery_method',
                'delivery_notes',
                'executed_works',
                'report_notes',
                'recommendations',
            ]);
        });
    }
};
