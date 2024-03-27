<?php

namespace App\Http\Controllers;

use App\Models\Capitation;
use App\Models\CapitationGroup;
use App\Models\Enrolee;
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
                    $visitData['sex'], $visitData['phone'], $visitData['lga_id'], $visitData['ward_id'],
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
                            'reporting_month'=>Carbon::parse($visitData['reporting_month'])->format('Y-m-d'),
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
            $capitation = Capitation::find($request->get('capitation_id'));
            $cap = CapitationGroup::find($capitation->group_id);
            MedicalBill::updateOrCreate([
                "facility_id"=>$user->provider_id,
                "month"=>Carbon::parse($cap->cap_year.'-'.$cap->month.'1')->format('Y-m-d')             
            ],[                
                "capitation_id"=> $request->get('capitation_id'),
                "amount"=>  $request->get('amount')
            ]);
            return new UtilResource('Saved successful', false, 200);                           
        }catch(\Exception $e){
            return new UtilResource($e->getMessage(), false, 400);                   
        }
    }

    public function index(Request $request)
    {           
        $external_db  = env('EX_DB_DATABASE');
      /*
        $internal_db  = env('DB_DATABASE');
        $enroleeQuery = DB::table($external_db.'.tbl_enrolee as enrolee')
        ->selectRaw(
            'enrolee.id,enrolee.enrolment_number, enrolee.lga,enrolee.ward,enrolee.provider_id, p.hcpname as facility, w.ward as ward_name, l.lga as lga_name, CONCAT(enrolee.first_name, " ", enrolee.surname) as full_name' 
        )->join($external_db.'.lga as l','l.id','enrolee.lga')
        ->join($external_db.'.ward as w','w.id','enrolee.ward')
        ->join($external_db.'.tbl_providers as p','p.id','enrolee.provider_id');
        
        // Filter enrollees with at least one visit
        $enroleeQuery->whereExists(function ($query) use ($internal_db, $external_db) {
            $query->select(DB::raw(1))
                ->from($internal_db.'.enrolee_visits')
                ->whereColumn($internal_db.'.enrolee_visits.nicare_id', '=', 'enrolee.enrolment_number');
        });
    
        // Paginate enrollees
        $paginatedEnrolees = $enroleeQuery->paginate(50);
    
        $enrolmentNumbers = $paginatedEnrolees->pluck('enrolment_number')->toArray();
    
        // Fetch visits for these enrolment numbers
        $visits = DB::table($internal_db.'.enrolee_visits', 'ev')
                    ->selectRaw('ev.*, pc.case_name as service')
                    ->join($external_db.'.tbl_programme_case as pc', 'pc.id','ev.service_accessed')
                    ->whereIn('nicare_id', $enrolmentNumbers)
                    ->get()
                    ->groupBy('nicare_id');
    
        // Attach visits to each enrollee
        $paginatedEnrolees->getCollection()->transform(function ($enrolee) use ($visits) {
            $enroleeVisits = $visits->get($enrolee->enrolment_number) ?? collect([]);
            $enrolee->visits = $enroleeVisits;
            return $enrolee;
        }); */
    /* 
        $perPage = 100;
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
    
        // Subquery to get the minimum id for each nicare_id (assuming id is a unique identifier of visits)
        $subQuery = EnroleeVisit::selectRaw('MIN(id) as id')
            ->groupBy('nicare_id');
    
        // Main query to join with the subquery and get the first visit details for each nicare_id
        $enroleeVisits = EnroleeVisit::joinSub($subQuery, 'first_visits', function ($join) {
            $join->on('enrolee_visits.id', '=', 'first_visits.id');
        })
        ->select('enrolee_visits.*') // Select the columns you need
        ->offset($offset)
        ->limit($perPage)
        ->get();
    
        // Count distinct nicare_id for total pagination
        $totalDistinct = EnroleeVisit::distinct('nicare_id')->count();
    
        // Manual pagination
        $enroleeVisitsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $enroleeVisits,
            $totalDistinct,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
     */
        $dateRange = $request->input('dateRange');
        if(empty($dateRange)){            
            $dateRange[0] = Carbon::parse('1995-01-01')->format('Y-m-d');
            $dateRange[1] = Carbon::now()->format('Y-m-d');
        }else{
            $dateRange[0] = Carbon::parse($dateRange[0])->format('Y-m-d');
            $dateRange[1] = Carbon::parse($dateRange[1])->format('Y-m-d');
        }     
        if(!empty($request->input("search"))){
            $search = $request->input("search");
            $enroleeVisits = EnroleeVisit::join($external_db.'.tbl_providers as f', 'f.id','enrolee_visits.facility_id')
                    ->join($external_db.'.lga as l', 'l.id','enrolee_visits.lga')
                    ->join($external_db.'.ward as w', 'w.id','enrolee_visits.ward')
                    ->join($external_db.'.tbl_programme_case as p', 'p.id','enrolee_visits.service_accessed')
                    ->join($external_db.'.tbl_enrolee as e', 'e.enrolment_number','enrolee_visits.nicare_id')
                    ->selectRaw("enrolee_visits.*")                    
                    ->whereRaw("e.first_name like '%$search%' OR e.surname like '%$search%' OR f.hcpname like '$search' OR p.case_name LIKE '%$search%' OR l.lga = '$search' OR w.ward LIKE '$search'     ")
                    ->whereBetween('date_of_visit',$dateRange)->paginate(50);
        }else{
            $enroleeVisits = EnroleeVisit::whereBetween('date_of_visit',$dateRange)->paginate(50);
        }
        return response()->json($enroleeVisits);
    }
    
    
}