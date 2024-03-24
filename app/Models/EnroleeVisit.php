<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnroleeVisit extends Model
{
    protected $connection = 'mysql';
    use HasFactory, Filterable;
    protected $fillable = [        
        'nicare_id',
        'name_of_enrolee',
        'sex',
        'phone',
        'lga',
        'ward',
        'facility_id',
        'reason_for_visit',
        'service_accessed',
        'date_of_visit',
        'reporting_month',
        'activated_user_id',
        'referred'
    ];
    protected $casts = [
        'reporting_month'=>'datetime:F, Y'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection('mysql');
    }
    public function getLgaNameAttribute(){
        return Lga::find($this?->lga)?->lga;
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
        return Facility::find($this?->facility_id)?->hcpname;
    }

    public function getServiceAttribute(){
        return Service::find($this?->service_accessed)?->case_name;
    }
    
    public function getNameOfEnroleeAttribute(){
        $enrolee = Enrolee::where('enrolment_number', $this?->nicare_id)->first();
        return $enrolee?->first_name . " " .  $enrolee?->other_name . " " . $enrolee?->surname;
    }

    public function user(){
        return $this->belongsTo(ActivatedUser::class,'activated_user_id');
    }

    public function getVisitCountAttribute()
    {
        // Count the number of visits with the same nicare_id
        return self::where('nicare_id', $this->nicare_id)->count();
    }

    protected $appends = ['lga_name', 'ward_name', 'facility', 'name_of_enrolee', 'service','visit_count'];
}
