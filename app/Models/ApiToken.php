<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    use HasFactory;
    protected $table ='apitokens';
    protected $connection = 'external_db';
    protected $fillable = ['_token'];

}
