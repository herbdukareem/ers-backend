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
        return Ward::find(intval($this->ward))?->ward;
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

    protected $appends = ['lga_name', 'ward_name', 'facility','nicare_id','name_of_enrolee'];
}
