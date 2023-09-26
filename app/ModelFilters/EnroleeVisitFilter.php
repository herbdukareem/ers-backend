<?php 

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
class EnroleeVisitFilter extends ModelFilter
{
    protected $drop_id = false;
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];

/*     public function firstName($value)
    {            
       return $this->where('first_name','LIKE', "%$value%");
    }

    public function lastName($value)
    {
       return $this->where('last_name','LIKE', "%$value%");
    } */


    public function lga($value){
        return $this->where('lag', $value);
    }

    public function ward($value){
        return $this->where('ward', $value);
    }

    public function dateOfVisit($value){
        return $this->whereDate('date_of_visit', $value);
    }

    public function reportingMonth($value){
        return $this->where('reporting_month', $value);
    }    

    public function reasonOfVisit($value){
        return $this->whereDate('reason_of_visit', $value);
    }

    public function createdAt($value)
    {           
        if (is_array($value)) {
            if ($value[0] == '') {
                $value[0] = '=';
            }
        
            if (!empty($value[1])) {                                                    
                return $this->whereDate('created_at',$value[0], $value[1]);
            }
        }else{
            return $this->whereDate('created_at',$value);
        }
    } 

 
}
