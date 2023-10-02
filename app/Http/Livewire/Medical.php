<?php

namespace App\Http\Livewire;

use App\Models\MedicalBill;
use Livewire\Component;
use Livewire\WithPagination;

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
        $this->chartData = json_encode($medicaBillsData->get()->groupBy('month'));
        $this->emit('chatkeyUpdated', $this->chartData);
        return view('livewire.medical',[
            'medical_bills' => $medicaBills
        ]);
    }

    public function clear(){
        $this->dateRange = null;
    }
    
}
