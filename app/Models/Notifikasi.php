<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class Notifikasi extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'read_at',
    ];


    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // ===== Helper =====
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function getTitleAttribute()
    {
        return $this->data['title'] ?? 'Notifikasi';
    }

    public function getMessageAttribute()
    {
        return $this->data['message'] ?? '-';
    }

    public function getNoPoAttribute()
    {
        return $this->data['no_po'] ?? null;
    }

    public function getUrlAttribute()
    {
        return $this->data['url'] ?? null;
    }
}
