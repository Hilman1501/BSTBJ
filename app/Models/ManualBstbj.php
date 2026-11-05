<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ManualBstbj extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'manual_bstbj';
    protected $guarded = [];
    protected $casts = [
        'tgl_produksi' => 'datetime',
        'confirmed_at' => 'datetime',
    ];
}
