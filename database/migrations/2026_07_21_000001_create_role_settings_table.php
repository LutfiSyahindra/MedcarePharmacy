<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->unique()->constrained('roles')->cascadeOnDelete();
            $table->boolean('can_view_all_branches')->default(false);
            $table->boolean('is_approver')->default(false);
            $table->string('approval_scope', 24)->default('same_branch');
            $table->boolean('receives_notifications')->default(false);
            $table->string('notification_scope', 24)->default('same_branch');
            $table->timestamps();

            $table->index(['is_approver', 'approval_scope']);
            $table->index(['receives_notifications', 'notification_scope']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_settings');
    }
};
