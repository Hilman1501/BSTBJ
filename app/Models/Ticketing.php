<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Ticketing extends Model
{
    protected $connection = 'mongodb_ticketing';
    protected $collection = 'ticketings';

    protected $guarded = []; // read-only, jadi nggak perlu strict fillable
}