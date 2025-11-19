<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchModel extends Model
{
    use HasFactory;

    protected $table = 'branches';
    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'email',
        'is_active',
    ];

    /**
     * Relasi: Branch punya banyak User
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_branches',
            'branch_id',
            'user_id'
        );
    }


}
