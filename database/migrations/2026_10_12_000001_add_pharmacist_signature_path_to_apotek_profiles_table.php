<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apotek_profiles', function (Blueprint $table) {
            $table->string('pharmacist_signature_path')->nullable()->after('pharmacist_license_number');
        });
    }

    public function down(): void
    {
        Schema::table('apotek_profiles', function (Blueprint $table) {
            $table->dropColumn('pharmacist_signature_path');
        });
    }
};
