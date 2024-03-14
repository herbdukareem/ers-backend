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

    public function mount()
    {
        $this->chatkey = 4;
    }

    public function render()
    {
        $enroleeVisits = EnroleeVisit::query();
        $enroleeVisitsData = EnroleeVisit::query();
        $report = EnroleeVisit::query();
        $lgas = LGA::all();

        $this->applyDateFilter($enroleeVisits, $enroleeVisitsData, $report);
        $wards =  $this->getFilteredWards($enroleeVisits, $enroleeVisitsData, $report);   
        
        $facilities = $this->getFilteredFacilities($enroleeVisits, $enroleeVisitsData, $report);      

        if($this->searchFacility){
            $enroleeVisits->where('facility_id',$this->searchFacility);
            $enroleeVisitsData->where('facility_id',$this->searchFacility);                
            $report->where('facility_id',$this->searchFacility);                
        }

        $this->processAndPrepareData($enroleeVisitsData, $report);
        //dd($enroleeVisitsData,  $this->getReasons($enroleeVisitsData));    
        $this->chatkey += 1; 
        return view('livewire.visits', [
            'enroleeVisits' => $enroleeVisits->paginate(3),
            'reasons' => $this->getReasons($enroleeVisitsData),
            'totalCounts' => $this->getTotalCounts($enroleeVisitsData),
            'reporting_month' => $this->getReportingMonths($enroleeVisitsData),
            'maleCounts' => $this->getGenderCounts($enroleeVisitsData, 'male'),
            'femaleCounts' => $this->getGenderCounts($enroleeVisitsData, 'female'),
            'referred' => $this->getReferredCount($report),
            'not_referred' => $this->getNotReferredCount($report),
            'lgas' => $lgas,
            'wards' => $wards,
            'facilities' => $facilities,
        ]);
    }
    

    // ... (exportData and clear methods remain the same)

    // New Methods for Data Processing and Preparation

    private function processAndPrepareData(&$enroleeVisitsData, &$report)
    {
        $enroleeVisitsData = $this->getEnroleeVisitsDataStatistics($enroleeVisitsData);
        $report = $this->getReportStatistics($report);
        $this->prepareChartDataAndEmitEvent($enroleeVisitsData);
    }

    private function getEnroleeVisitsDataStatistics($enroleeVisitsData)
    {
        return $enroleeVisitsData->selectRaw("SUM(IF(sex ='male',1,0)) AS male, SUM(IF(sex ='female',1,0)) AS female, COUNT(id) AS total_count,reporting_month, service_accessed ")
            ->groupBy('service_accessed', 'reporting_month')
            ->get();
    }

    private function getReportStatistics($report)
    {
        return $report->selectRaw("SUM(IF(referred ='no',1,0)) AS no_r, SUM(IF(referred ='yes',1,0)) AS yes_r ")
            ->first();
    }

    private function prepareChartDataAndEmitEvent($enroleeVisitsData)
    {
        // Data preparation for chart
        $this->chartData = json_encode($enroleeVisitsData);
        $this->emit('chatkeyUpdated', $this->chartData);
    }

    // Helper methods for filters, wards, facilities, etc.
    private function applyDateFilter(&$enroleeVisits, &$enroleeVisitsData, &$report)
    {
        if ($this->dateRange) {
            $date = Carbon::parse($this->dateRange)->format('F, Y');
            $enroleeVisits->where('reporting_month', $date);
            $enroleeVisitsData->where('reporting_month', $date);
            $report->where('reporting_month', $date);
        }
    }

    private function getFilteredWards(&$enroleeVisits, &$enroleeVisitsData,&$report)
    {
        $wards = [];
        if ($this->searchLga) {            
            $enroleeVisits->where('lga',$this->searchLga);
            $enroleeVisitsData->where('lga',$this->searchLga);
            $report->where('lga',$this->searchLga);
            $wards = Ward::where('lga_id', $this->searchLga)->get();
        }
        return $wards;
    }

    private function getFilteredFacilities(&$enroleeVisits, &$enroleeVisitsData,&$report)
    {
        $facilities = [];
        if ($this->searchWard) {
            $enroleeVisits->where('ward',$this->searchWard);
            $enroleeVisitsData->where('ward',$this->searchWard);
            $report->where('ward',$this->searchWard);
            $facilities = Facility::where('hcpward', $this->searchWard)->get();
        }
        return $facilities;
    }

    private function getReasons($enroleeVisitsData)
    {
        return $enroleeVisitsData->pluck('service_accessed')->toArray();
    }

    private function getTotalCounts($enroleeVisitsData)
    {
        return $enroleeVisitsData->pluck('total_count')->toJson();
    }

    private function getReportingMonths($enroleeVisitsData)
    {
        return $enroleeVisitsData->pluck('reporting_month')->toJson();
    }

    private function getGenderCounts($enroleeVisitsData, $gender)
    {
        return $enroleeVisitsData->pluck($gender)->toJson();
    }

    private function getReferredCount($report)
    {
        return $report->yes_r;
    }

    private function getNotReferredCount($report)
    {
        return $report->no_r;
    }

    private function applyFilters($query)
    {
        if ($this->dateRange) {
            $date = Carbon::parse($this->dateRange)->format('F, Y');
            $query->where('reporting_month', $date);
        }

        if ($this->searchLga) {
            $query->where('lga', $this->searchLga);
        }

        if ($this->searchWard) {
            $query->where('ward', $this->searchWard);
        }

        if ($this->searchFacility) {
            $query->where('facility_id', $this->searchFacility);
        }

        return $query;
    }

    private function formatExportData($enroleeVisits)
    {
        $filteredData = $enroleeVisits->map(function ($visit) {
            return collect($visit)->except([
                'activated_user_id',
                'lga',
                'ward',
                'facility_id',
                'updated_at',
                'service_accessed',
            ])->toArray();
        })->toArray();
    
        array_unshift($filteredData, array_keys($filteredData[0]));
    
        return $filteredData;
    }
    
    public function clear()
    {
        $this->dateRange = null;
        $this->chatkey += 1;
        $this->searchLga = null;
        $this->searchWard = null;
        $this->searchFacility = null;
    }

    public function exportData()
    {
        $enroleeVisits = $this->applyFilters(EnroleeVisit::query())->get();

        if ($enroleeVisits->count() > 0) {
            $formattedData = $this->formatExportData($enroleeVisits);
            $response = Excel::download(new ExportsEnroleeVisit(collect($formattedData)), 'visits.xlsx');
            ob_end_clean();
            return $response;
        }
    }

}
