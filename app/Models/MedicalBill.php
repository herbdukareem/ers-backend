<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalBill extends Model
{
    use HasFactory;

    protected $casts = [
        'month'=>'datetime: F, Y'
    ];
    
    protected $fillable = [
        "month",
        "amount",
        "main_amount",
        "remaining_amount",
        "facility_id",
    ];

    public function getFacilityAttribute(){
        return Facility::find($this?->facility_id)?->hcpname;
    }

    protected $appends = ['facility'];
}
