<?php

namespace App\Http\Livewire;

use App\Exports\EnroleeVisit as ExportsEnroleeVisit;
use App\Models\EnroleeVisit;
use App\Models\Facility;
use App\Models\LGA;
use App\Models\Ward;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Visits extends Component
{
    use WithPagination;
   
    public $dateRange;
    public $chartData;
    public $chatkey;

    public $searchLga;
    public $searchWard;
    public $searchFacility;
    
    public function mount(){
        $this->chatkey = 4;
    }

    public function render()
    {
        $enroleeVisits = EnroleeVisit::query();
        $enroleeVisitsData = EnroleeVisit::query();
        $report = EnroleeVisit::query();
        $lgas = LGA::all();       

        if ($this->dateRange) {            
            $date = Carbon::parse($this->dateRange)->format('F, Y');            
            $enroleeVisits->where('reporting_month', $date);
            $enroleeVisitsData->where('reporting_month', $date);
            $report->where('reporting_month', $date);
            //dd($enroleeVisitsData->groupBy('reason_of_visit','reporting_month')->get()->toArray());
        }
                        
            $wards = [];
            $facilities = [];
            if($this->searchLga){
                $enroleeVisits->where('lga',$this->searchLga);
                $enroleeVisitsData->where('lga',$this->searchLga);
                $report->where('lga',$this->searchLga);
                $wards = Ward::where('lga_id', $this->searchLga)->get();                                
            }
            if($this->searchWard){
                $enroleeVisits->where('ward',$this->searchWard);
                $enroleeVisitsData->where('ward',$this->searchWard);
                $report->where('ward',$this->searchWard);
                $facilities =Facility::where('hcpward', $this->searchWard)->get();
            }
            if($this->searchFacility){
                $enroleeVisits->where('facility_id',$this->searchFacility);
                $enroleeVisitsData->where('facility_id',$this->searchFacility);                
                $report->where('facility_id',$this->searchFacility);                
            }
        $this->chatkey = mt_rand(1000,99999);
        $enroleeVisits = $enroleeVisits->paginate(3);
       
        $enroleeVisitsData = $enroleeVisitsData->selectRaw("SUM(IF(sex ='male',1,0)) AS male, SUM(IF(sex ='female',1,0)) AS female, COUNT(id) AS total_count,reporting_month, service_accessed ")->groupBy('service_accessed','reporting_month')->get();

        $reasons = $enroleeVisitsData->pluck('service')->toArray();
        $report = $report->selectRaw("SUM(IF(referred ='no',1,0)) AS no_r, SUM(IF(referred ='yes',1,0)) AS yes_r ")->first();
        
        $referred = $report->yes_r;
        $notreferred = $report->no_r;
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
            'referred'=>$referred, 
            'not_referred'=>$notreferred,       
            'lgas'=>$lgas ,
            'wards'=>$wards,
            'facilities'=> $facilities
        ]);
    }

    public function exportData(){
        $enroleeVisits = EnroleeVisit::query();        

        if ($this->dateRange) {                        
            $date = Carbon::parse($this->dateRange)->format('F, Y');            
            $enroleeVisits->where('reporting_month', $date);            
                
        }        
        if($this->searchLga){
            $enroleeVisits->where('lga',$this->searchLga);            
        }
        if($this->searchWard){
            $enroleeVisits->where('ward',$this->searchWard);            
        }
        if($this->searchFacility){
            $enroleeVisits->where('facility',$this->searchFacility);            
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
                unset($item['service_accessed']);            
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
