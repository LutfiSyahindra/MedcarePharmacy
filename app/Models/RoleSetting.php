<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class RoleSetting extends Model
{
    protected $fillable = [
        'role_id',
        'can_view_all_branches',
        'pos_scope',
        'is_approver',
        'approval_scope',
        'is_stock_opname_validator',
        'can_view_stock_during_opname',
        'receives_notifications',
        'notification_scope',
    ];

    protected $casts = [
        'can_view_all_branches' => 'boolean',
        'is_approver' => 'boolean',
        'is_stock_opname_validator' => 'boolean',
        'can_view_stock_during_opname' => 'boolean',
        'receives_notifications' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
