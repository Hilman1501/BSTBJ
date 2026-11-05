<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class HistoricalBstbj extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'historical_bstbj';
    protected $guarded = [];

    protected $attributes = [
        'status_rpa' => 'On Progress SAP',
        'rpa_execution' => 0,
    ];
    
    protected $casts = [
        'tgl_produksi' => 'datetime',
        'confirmed_at' => 'datetime',
    ];
}