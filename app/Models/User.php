<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Passport\HasApiTokens;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $connection = 'external_db';
    protected $fillable = [        
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',        
    ];

    public function getLgaNameAttribute(){
        return Lga::find($this->lga)?->lga;
    }

    public function getWardNameAttribute(){
        $name = Ward::find($this->ward)?->ward;
        if(empty($name)){
            try{
                $name = Ward::where('sn',$this->ward)?->wards;
            }catch(\Exception $e){
                
            }
        }
        return $name;
    }


    public function getFacilityAttribute(){
        return Facility::find($this->facility_id)?->hcpname;
    }

    protected $appends = ['lga_name', 'ward_name', 'facility'];

}
