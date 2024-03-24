<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrolee extends Model
{
    use HasFactory;
    protected $connection = 'external_db';
    protected $table = 'tbl_enrolee';
    protected $fillable = [
        'phone_number'
    ];

    public function getLgaNameAttribute(){        
        return Lga::find(intval($this->lga))?->lga;
    }

    public function getWardNameAttribute(){
        $name = Ward::find(intval($this->ward))?->ward;
        if(empty($name)){
            try{
                $name = Ward::where('sn',$this->ward)?->wards;
            }catch(\Exception $e){
                
            }
        }
        return $name;
    }

    public function getFacilityAttribute(){
        return Facility::find(intval($this->provider_id))?->hcpname;
    }

    public function getNameOfEnroleeAttribute(){        
        return $this?->first_name . " " .  $this?->other_name . " " . $this?->surname;
    }

    public function getNicareIdAttribute(){
        return $this->enrolment_number;
    }

    public function getFullNameAttribute(){
        return $this->first_name +' '+ $this->surname;
    }

    public function visits()
    {
        return $this->hasMany(EnroleeVisit::class, 'enrollment_number', 'nicare_id');
    }

    protected $appends = ['lga_name', 'ward_name', 'facility','nicare_id','name_of_enrolee'];
}
