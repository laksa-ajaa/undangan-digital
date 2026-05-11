<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('guest_wishes')) {
            DB::statement("UPDATE guest_wishes SET attendance_status = 'tidak_hadir' WHERE attendance_status IN ('berhalangan', 'ragu')");
            DB::statement("ALTER TABLE guest_wishes MODIFY attendance_status ENUM('hadir', 'tidak_hadir') NOT NULL");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('guest_wishes')) {
            DB::statement("ALTER TABLE guest_wishes MODIFY attendance_status ENUM('hadir', 'berhalangan', 'ragu') NOT NULL");
        }
    }
};
