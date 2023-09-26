<?php 

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
class UserFilter extends ModelFilter
{
    protected $drop_id = false;
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];
    public function universityId($value)
    {
       return $this->whereRaw("EXISTS (SELECT 1 FROM users_programmes u WHERE u.student_id = users.id AND u.university_id = $value)");
    }


    public function firstName($value)
    {            
       return $this->where('first_name','LIKE', "%$value%");
    }

    public function lastName($value)
    {
       return $this->where('last_name','LIKE', "%$value%");
    }

    public function address($value){
        return $this->join('high_schools as u2', 'u2.id','users.high_school_id')->where('u2.address', "$value");
    }
    
    public function countryId($value){
        return $this->join('high_schools as u', 'u.id','users.high_school_id')->where('u.country_id', "$value");
    }
    

    public function stateId($value){
        return $this->where('state_id', $value);
    }

    public function highSchoolId($value){
        return $this->where('high_school_id', $value);
    }

    public function status($value){
        return $this->where('application_status', $value);
    }

    public function uniqueId($value){        
        return $this->where('unique_id','LIKE', "%$value%");
    }

    public function programStatus($value)
    {     
        return $this->whereHas('programs', function ($query) use ($value) {
            $query->where('application_status', $value);
        });
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

    public function programCreatedAt($value)
    {
        if (is_array($value)) {
            if ($value[0] == '') {
                $value[0] = '=';
            }
        
            if (!empty($value[1])) {                
                return $this->whereHas('programs', function ($query) use ($value) {
                    $query->whereDate('created_at', $value[0], $value[1]);
                });
            }
        }else{
            return $this->whereHas('programs', function ($query) use ($value) {
                $query->whereDate('created_at', $value);
            });    
        }
    
    }

 
}
