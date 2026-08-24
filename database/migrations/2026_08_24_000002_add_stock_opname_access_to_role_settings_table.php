<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_settings', function (Blueprint $table) {
            $table->boolean('is_stock_opname_validator')->default(false)->after('approval_scope');
            $table->boolean('can_view_stock_during_opname')->default(false)->after('is_stock_opname_validator');

            $table->index('is_stock_opname_validator', 'role_settings_opname_validator_index');
            $table->index('can_view_stock_during_opname', 'role_settings_opname_stock_view_index');
        });

        DB::table('role_settings')
            ->whereIn('role_id', function ($query) {
                $query->select('id')
                    ->from('roles')
                    ->whereIn(DB::raw('LOWER(name)'), ['admin', 'apoteker', 'super admin']);
            })
            ->update(['is_stock_opname_validator' => true]);
    }

    public function down(): void
    {
        Schema::table('role_settings', function (Blueprint $table) {
            $table->dropIndex('role_settings_opname_validator_index');
            $table->dropIndex('role_settings_opname_stock_view_index');
            $table->dropColumn(['is_stock_opname_validator', 'can_view_stock_during_opname']);
        });
    }
};
