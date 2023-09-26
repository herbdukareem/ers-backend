<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Passport\HasApiTokens;

class ActivatedUser extends Authenticatable
{
    use HasFactory, HasApiTokens;
    protected $fillable = [
        'user_id',
        'activation_code',
        'facility_id',
        'lga',
        'ward',
    ];

    public function getFacilityAttribute(){
        return Facility::find($this->facility_id)?->hcpname;
    }

    public function getLgaNameAttribute(){
        return Lga::find($this->lga)?->lga;
    }

    public function getWardNameAttribute(){
        return Ward::find($this->ward)?->ward;
    }

    protected $appends = ['lga_name', 'ward_name', 'facility'];
}
