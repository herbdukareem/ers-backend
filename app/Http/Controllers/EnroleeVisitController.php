<?php

namespace App\Http\Controllers;

use App\Models\Capitation;
use App\Models\EnroleeVisit;
use App\Models\MedicalBill;
use App\Models\User;
use App\Transformers\UtilResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class EnroleeVisitController extends Controller
{
    

    public function storeBulk(Request $request)
    {
        try{
        
        $data = $request->input('enrolee_visits');
        DB::beginTransaction();
        if (!empty($data) && is_array($data)) {
            // Ensure the data follows the expected structure
            $validatedData = [];
            foreach ($data as $visitData) {
                if (
                    isset($visitData['nicare_id'],
                    $visitData['sex'], $visitData['phone'], $visitData['lga'], $visitData['ward'],
                    $visitData['facility_id'], $visitData['reason_for_visit'], $visitData['date_of_visit'],
                    $visitData['reporting_month'])
                ) {
                    foreach($visitData['service_accessed'] as $seviceId) { 
                        $validatedData[] =[
                            'activated_user_id'=>$request->input('activated_user_id'),
                            'nicare_id'=>$visitData['nicare_id'],                                                                        
                            'lga'=>$visitData['lga_id'],
                            'ward'=>$visitData['ward_id'],
                            'sex'=>$visitData['sex'],
                            'facility_id'=>$visitData['facility_id'],
                            'phone'=>$visitData['phone'],
                            'referred'=>$visitData['referred']??'no',
                            'reason_for_visit'=> $visitData['reason_for_visit'],
                            'service_accessed'=> $seviceId,
                            'date_of_visit'=>Carbon::parse($visitData['date_of_visit'])->format('Y-m-d'),
                            'reporting_month'=>Carbon::parse($visitData['reporting_month'])->format('F, Y'),
                            'created_at'=>Carbon::now(),
                        ];                        
                    }
                }
            }
            DB::commit();                 
                // Insert the validated data in bulk
                if (!empty($validatedData)) {
                    EnroleeVisit::insert($validatedData);
                    return new UtilResource('Bulk insert successful', false, 200);                           
                }
            }    

        }catch(\Exception $e){
            DB::rollBack();
            return new UtilResource($e->getMessage(), false, 400);                   
        }
    }

    public function fetchVisits(Request $request){
        try {
            $filters = $request->get('filters');
            if($filters['paginate']??false){
                $response  = EnroleeVisit::filter($filters??[])->paginate(20);
            }else{
                //$filters['reporting_month'] = Carbon::now()->format('F, Y');
                // Get the first day of the current month
                $firstDayOfMonth = Carbon::now()->startOfMonth();

                // Get the last day of the current month
                $lastDayOfMonth = Carbon::now()->endOfMonth();

              /*   // Use the first and last day of the month to filter records
                $filters['created_at'] = [
                    '>=', // Greater than or equal to the first day of the month
                    ,
                    '<=', // Less than or equal to the last day of the month
                    ,
                ]; */
                $response = EnroleeVisit::whereBetween('created_at', [$firstDayOfMonth, $lastDayOfMonth])->get();
            }
            return new UtilResource($response, false, 200);                   
        } catch (\Exception $e) {
            return new UtilResource($e->getMessage(), false, 400);                   
        }
    }

    public function medsSave(Request $request){
        try{
            $user = User::find($request->get('user_id'));
            $totalCaps = Capitation::where('provider_id', $user->facility_id)->sum('total_cap') ?? 0;
            MedicalBill::updateOrCreate([
                "facility_id"=>$user->provider_id,
                "month"=>$request->get('month')                
            ],[
                "remaining_amount"=>  $totalCaps - $request->get('amount'),
                "main_amount"=>  $totalCaps,
                "amount"=>  $request->get('amount')
            ]);
            return new UtilResource('Saved successful', false, 200);                           
        }catch(\Exception $e){
            return new UtilResource($e->getMessage(), false, 400);                   
        }
    }
}