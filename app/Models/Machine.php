<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Machine extends Model
{
    protected $connection = 'mongodb_ticketing';
    protected $collection = 'machines';
    protected $guarded = [];
}