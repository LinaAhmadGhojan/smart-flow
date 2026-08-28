<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('phone');
        });

        Schema::create('engineers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('phone');
        });

        $this->backfillCustomers();
        $this->backfillEngineers();
    }

    private function backfillCustomers(): void
    {
        $seen = [];

        $fromSlots = DB::table('appointment_slots')
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->select('customer_name', 'customer_phone', 'customer_email')
            ->get();

        $fromStudyRequests = Schema::hasTable('study_requests')
            ? DB::table('study_requests')
                ->whereNotNull('customer_name')
                ->where('customer_name', '!=', '')
                ->select('customer_name', 'customer_phone')
                ->get()
            : collect();

        foreach ($fromSlots->concat($fromStudyRequests) as $row) {
            $key = $row->customer_name . '|' . ($row->customer_phone ?? '');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            DB::table('customers')->insert([
                'name' => $row->customer_name,
                'phone' => $row->customer_phone ?? null,
                'email' => $row->customer_email ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function backfillEngineers(): void
    {
        $seen = [];

        $fromSlots = DB::table('appointment_slots')
            ->whereNotNull('engineer_name')
            ->where('engineer_name', '!=', '')
            ->select('engineer_name', 'engineer_phone')
            ->get();

        foreach ($fromSlots as $row) {
            $key = $row->engineer_name . '|' . ($row->engineer_phone ?? '');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            DB::table('engineers')->insert([
                'name' => $row->engineer_name,
                'phone' => $row->engineer_phone ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('engineers');
        Schema::dropIfExists('customers');
    }
};
