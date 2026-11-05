<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model as Eloquent;

class Test extends Eloquent
{
    protected $connection = 'mongodb'; // Tentukan koneksi MongoDB
}
