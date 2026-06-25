<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE purchase_orders MODIFY status ENUM('draft','waiting_approval','approved','rejected','diterima_sebagian','selesai') DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE purchase_orders SET status = 'approved' WHERE status IN ('diterima_sebagian','selesai')");
            DB::statement("ALTER TABLE purchase_orders MODIFY status ENUM('draft','waiting_approval','approved','rejected') DEFAULT 'draft'");
        }
    }
};
