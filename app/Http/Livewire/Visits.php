<?php

namespace App\Http\Livewire;

use App\Exports\EnroleeVisit as ExportsEnroleeVisit;
use App\Models\EnroleeVisit;
use Livewire\Component;
use Livewire\withPagination;
use Maatwebsite\Excel\Facades\Excel;

class Visits extends Component
{
    use withPagination;
   
    public $dateRange;
    public $chartData;
    public $chatkey;

    public function mount(){
        $this->chatkey = 4;
    }

    public function render()
    {
        $enroleeVisits = EnroleeVisit::query();
        $enroleeVisitsData = EnroleeVisit::query();
                        

        if ($this->dateRange) {                        
                if(is_array($this->dateRange)){
                    $enroleeVisits->whereBetween('date_of_visit', $this->dateRange)
                    ->orWhere('date_of_visit', '=', $this->dateRange[0])
                    ->orWhere('date_of_visit', '=', $this->dateRange[1]);
                    $enroleeVisitsData->whereBetween('date_of_visit', $this->dateRange)
                    ->orWhere('date_of_visit', '=', $this->dateRange[0])
                    ->orWhere('date_of_visit', '=', $this->dateRange[1]);
                    //dd($enroleeVisitsData->groupBy('reason_of_visit','reporting_month')->get()->toArray());
                }
                
        }        
        $this->chatkey = mt_rand(1000,99999);
        $enroleeVisits = $enroleeVisits->paginate(3);
        $enroleeVisitsData = $enroleeVisitsData->selectRaw("SUM(IF(sex ='male',1,0)) AS male, SUM(IF(sex ='female',1,0)) AS female, COUNT(id) AS total_count,reporting_month, reason_of_visit ")->groupBy('reason_of_visit','reporting_month')->get();

        $reasons = $enroleeVisitsData->pluck('reason')->toArray();
        $totalCounts = $enroleeVisitsData->pluck('total_count')->toArray();
        $reporting_month = $enroleeVisitsData->pluck('reporting_month')->toArray();
        $maleCounts = $enroleeVisitsData->pluck('male')->toArray();
        $femaleCounts = $enroleeVisitsData->pluck('female')->toArray(); 
        $this->chartData = json_encode($enroleeVisitsData);
        $this->emit('chatkeyUpdated', $this->chartData);
        return view('livewire.visits', [
            'enroleeVisits' => $enroleeVisits,
            'reasons' => json_encode($reasons),
            'totalCounts' => json_encode($totalCounts),
            'reporting_month' => json_encode($reporting_month),
            'maleCounts' => json_encode($maleCounts),
            'femaleCounts' => json_encode($femaleCounts),             
        ]);
    }

    public function exportData(){
        $enroleeVisits = EnroleeVisit::query();        

        if ($this->dateRange) {                        
                if(is_array($this->dateRange)){
                    $enroleeVisits->whereBetween('date_of_visit', $this->dateRange)
                    ->orWhere('date_of_visit', '=', $this->dateRange[0])
                    ->orWhere('date_of_visit', '=', $this->dateRange[1]);                    
                }
                
        }        
        
        $enroleeVisits = $enroleeVisits->get()->toArray();
        if(count($enroleeVisits)> 0){
            $i = 0;
            foreach ($enroleeVisits as &$item) {
                $i++;
                $item['id'] = $i;                
                unset($item['activated_user_id']);
                unset($item['lga']);
                unset($item['ward']);
                unset($item['facility_id']);
                unset($item['updated_at']);
                unset($item['reason_of_visit']);            
            }            
            $headers = $enroleeVisits[0];
            $headers = collect($headers)->keys()->mapWithKeys(function ($item) {
                return [$item => ucwords(str_replace('_', ' ', $item))];
            });
            
            // Convert the resulting key-value pairs to an associative array
            $headersArray = $headers->all();
                // Add the headers as the first item in the collection
            $enroleeVisits= collect($enroleeVisits)->prepend($headersArray);

            $response = Excel::download(new ExportsEnroleeVisit($enroleeVisits), 'visits.xlsx');
            ob_end_clean();
            return  $response;
        }
    }

    public function clear(){
        $this->dateRange = null;
    }
    
}
