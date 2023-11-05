<?php

namespace App\Http\Livewire;

use App\Exports\EnroleeVisit;
use App\Models\MedicalBill;

use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Medical extends Component
{
    use WithPagination;
   
    public $dateRange;
    public $chartData;
    public $chatkey;

    public function mount(){
        $this->chatkey = 4;
    }

    public function render()
    {
        $medicaBills = MedicalBill::query();    
        $medicaBillsData = MedicalBill::query();    
                        

        if ($this->dateRange) {                        
                if(is_array($this->dateRange)){
                    $medicaBills->whereBetween('month', $this->dateRange)
                    ->orWhere('month', '=', $this->dateRange[0])
                    ->orWhere('month', '=', $this->dateRange[1]);
                    $medicaBillsData->whereBetween('month', $this->dateRange)
                    ->orWhere('month', '=', $this->dateRange[0])
                    ->orWhere('month', '=', $this->dateRange[1]);                 
                }             
        } 

        $this->chatkey = mt_rand(1000,99999);
        $medicaBills = $medicaBills->paginate(3);
        $this->chartData = $medicaBillsData->get()->groupBy('month')->all();
        $this->emit('chatkeyUpdated', $this->chartData);
        return view('livewire.medical',[
            'medical_bills' => $medicaBills
        ]);
    }

    public function clear(){
        $this->dateRange = null;
    }
    
    public function exportData(){
        $medicaBillsData = MedicalBill::query();    
        if ($this->dateRange) {                        
                if(is_array($this->dateRange)){
                    $medicaBillsData->whereBetween('month', $this->dateRange)
                    ->orWhere('month', '=', $this->dateRange[0])
                    ->orWhere('month', '=', $this->dateRange[1]);                    
                }
                
        }        
        
        $medicaBillsData = $medicaBillsData->get()->toArray();
        if(count($medicaBillsData)> 0){
            $i = 0;
            foreach ($medicaBillsData as &$item) {
                $i++;
                $item['id'] = $i;                                
                unset($item['activated_user_id']);
                $item['Capitation'] = $item['main_amount'];
                $item['Provider'] = $item['facility'];
                unset($item['facility']);                
                unset($item['main_amount']);
                unset($item['remaining_amount']);                
                unset($item['facility_id']);                
                unset($item['created_at']);                
                unset($item['updated_at']);                
            }            
            $headers = $medicaBillsData[0];
            $headers = collect($headers)->keys()->mapWithKeys(function ($item) {
                return [$item => ucwords(str_replace('_', ' ', $item))];
            });
            
            // Convert the resulting key-value pairs to an associative array
            $headersArray = $headers->all();
                // Add the headers as the first item in the collection
            $medicaBillsData= collect($medicaBillsData)->prepend($headersArray);

            $response = Excel::download(new EnroleeVisit($medicaBillsData), 'medical_bills.xlsx');
            ob_end_clean();
            return  $response;
        }
    }

}
