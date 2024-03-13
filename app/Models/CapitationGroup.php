<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapitationGroup extends Model
{
    use HasFactory;
    protected $connection = 'external_db';
    protected $table = "capitation_grouping";
}
