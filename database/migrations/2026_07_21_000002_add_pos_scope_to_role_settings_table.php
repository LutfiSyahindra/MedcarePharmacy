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
            $table->string('pos_scope', 24)->default('same_branch')->after('can_view_all_branches');
        });

        DB::table('role_settings')
            ->whereIn('role_id', function ($query) {
                $query->select('id')
                    ->from('roles')
                    ->whereIn(DB::raw('LOWER(name)'), ['admin', 'super admin']);
            })
            ->update(['pos_scope' => 'all_branches']);
    }

    public function down(): void
    {
        Schema::table('role_settings', function (Blueprint $table) {
            $table->dropColumn('pos_scope');
        });
    }
};
