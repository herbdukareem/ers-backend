<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capitation extends Model
{
    use HasFactory;
    protected $connection = 'external_db';
}
