<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class EnroleeVisit implements FromCollection
{
    private $enroleeVisits;    
    public function __construct($enroleeVisits) {
        $this->enroleeVisits = $enroleeVisits;        
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public function collection()
    {     
        return $this->enroleeVisits;
    }

}
