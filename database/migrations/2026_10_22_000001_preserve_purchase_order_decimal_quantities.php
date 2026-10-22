<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->decimal('qty', 15, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->integer('qty')->default(0)->change();
        });
    }
};
