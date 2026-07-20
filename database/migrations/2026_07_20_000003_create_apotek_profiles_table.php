<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apotek_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->unique()->constrained('branches')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slogan')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('website')->nullable();
            $table->string('instagram', 100)->nullable();
            $table->text('address');
            $table->string('village', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('pharmacist_name', 150)->nullable();
            $table->string('pharmacist_license_number', 100)->nullable();
            $table->string('pharmacy_license_number', 100)->nullable();
            $table->date('license_expired_at')->nullable();
            $table->string('tax_id', 40)->nullable();
            $table->json('operational_hours')->nullable();
            $table->text('receipt_footer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apotek_profiles');
    }
};
