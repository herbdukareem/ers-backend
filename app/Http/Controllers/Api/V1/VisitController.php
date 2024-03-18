<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Models\Capitation;
use App\Models\CapitationGroup;
use App\Models\Enrolee;
use App\Models\EnroleeVisit;
use App\Models\LGA;
use App\Models\MedicalBill;
use Carbon\Carbon;
//use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    public function totalEncounters()
    {
        try {
            $totalEncounters = EnroleeVisit::count();
            return response()->json(['total_encounters' => $totalEncounters]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function EnrolleesAnalysis(Request $request)
    {
        try {
            $dateRange = $request->input('dateRange');
            if(empty($dateRange)){
                //$dateRange[0] = Carbon::now()->startOfMonth()->toDateString();
                //$dateRange[1] = Carbon::now()->endOfMonth()->toDateString();
                $dateRange[0] = Carbon::parse('1995-01-01')->format('Y-m-d');
                $dateRange[1] = Carbon::now()->format('Y-m-d');
            }else{
                $dateRange[0] = Carbon::parse($dateRange[0])->format('Y-m-d');
                $dateRange[1] = Carbon::parse($dateRange[1])->format('Y-m-d');
            }            
            // If mode_of_enrolment is in enrolee_visits
            $external_db  = env('EX_DB_DATABASE');
            $internal_db  = env('DB_DATABASE');
            $visitsGroupedByFunding = DB::table( $internal_db.'.enrolee_visits')
            ->join($external_db.'.tbl_enrolee', 'enrolee_visits.nicare_id', '=', $external_db.'.tbl_enrolee.enrolment_number')
            ->select($external_db.'.tbl_enrolee.mode_of_enrolment', DB::raw('COUNT(*) as total_visits'))
            ->groupBy($external_db.'.tbl_enrolee.mode_of_enrolment')
            ->whereBetween('enrolee_visits.date_of_visit',$dateRange)
            ->get();

            $visitsGroupedBySex =EnroleeVisit::select('sex', DB::raw('COUNT(*) as total_visits'))
            ->groupBy('sex')->whereBetween('date_of_visit',$dateRange)
            ->get()->pluck('sex','total_visits');

            // Counting distinct enrollees who have visits
            $totalEnrolleesAll = Enrolee::count();
            $totalEnrollees = EnroleeVisit::whereBetween('enrolee_visits.date_of_visit',$dateRange)->get();                    
            $totalDistinctEnrollees = $totalEnrollees->unique('nicare_id')->count();
            $totalReferrals = EnroleeVisit::where('referred', 'yes')
                    ->whereBetween('enrolee_visits.date_of_visit',$dateRange)
                    ->count();
    
            $top10Services = EnroleeVisit::select('service_accessed',DB::raw('COUNT(*) as total'))
            ->whereBetween('date_of_visit', [$dateRange[0], $dateRange[1]])
            ->groupBy('service_accessed')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->get()->pluck('total','service');  
            
            $top10Facilities = EnroleeVisit::select('facility_id',DB::raw('COUNT(*) as total'))
                ->whereBetween('date_of_visit', [$dateRange[0], $dateRange[1]])
                ->groupBy('facility_id')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(10)
                ->get()->pluck('total','facility');  

            $top10Wards = EnroleeVisit::select('ward',DB::raw('COUNT(*) as total'))
                ->whereBetween('date_of_visit', [$dateRange[0], $dateRange[1]])
                ->groupBy('ward')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(10)
                ->get()->pluck('total','ward_name');  

            $visitsGroupedByFunding->map(function($item){
                if($item->mode_of_enrolment == 'huwe'){
                    $item->mode_of_enrolment = 'BHCPF';
                    return $item;
                }
                if($item->mode_of_enrolment == 'premium'){
                    $item->mode_of_enrolment = 'NGSCHS';
                    return $item;
                }
                return $item;
            });

            return response()->json([
                'visits_by_mode_of_enrolment' => $visitsGroupedByFunding,
                'total_visits' => $totalEnrollees->count(),
                'total_distinct_visits' => $totalDistinctEnrollees,
                'visits_by_sex' => $visitsGroupedBySex,
                'total_referrals'=> $totalReferrals,
                'top_accessed' => $top10Services,
                'top_facility' => $top10Facilities,
                'top_wards' =>$top10Wards,
                "totalEnrolleesAll"=>number_format($totalEnrolleesAll)
            ]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }

    }

    public function medicalsBillsReport(Request $request)
    {
        try {
            $external_db = env('EX_DB_DATABASE');
            $internal_db = env('DB_DATABASE');
        
            $medicaBills = DB::table($internal_db . '.medical_bills as m')
                ->join($external_db . '.capitations as c', 'c.id', 'm.capitation_id')
                ->join($external_db . '.capitation_grouping as g', 'g.id', 'c.group_id');
        
            $dateRange = $request->input('dateRange');
            if (empty($dateRange)) {
                    //$dateRange[0] = Carbon::now()->startOfMonth()->toDateString();
                //$dateRange[1] = Carbon::now()->endOfMonth()->toDateString();
                $dateRange[0] = Carbon::parse('1995-01-01')->format('Y-m-d');
                $dateRange[1] = Carbon::now()->format('Y-m-d');
            }
        
            $medicaBills = $medicaBills->selectRaw('CONCAT(SUBSTRING(g.month_full,1,3),", ", g.cap_year) as date, SUM(total_cap) as cap_total_amount, SUM(m.amount) as total_medicalbill_amount')                
                ->groupBy('g.cap_year','g.month_full')
                ->orderBy('c.id')
                ->whereBetween('m.month', $dateRange)
                ->get();
            $totalMedical = $medicaBills->sum('total_medicalbill_amount');
            //$capitation = $medicaBills->sum('cap_total_amount');
            //$total = number_format($medicaBills->sum('cap_total_amount') - $medicaBills->sum('total_medicalbill_amount'),2,'.',',');
        
            return response()->json([
                'medicals' => $medicaBills,
                'medical_bill_amount'=>$totalMedical,
                //'capitation'=>$capitation,
                //"prosit" => $total, // Assuming total_main is not needed
            ]);
        } catch (\Exception $e) {            
            return response()->json($e->getMessage(), 400);
        }
        
    }

    public function encountersLastMonth()
    {
        try {
            $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
            $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();
            $totalEncountersLastMonth = EnroleeVisit::whereBetween('date_of_visit', [$startOfLastMonth, $endOfLastMonth])->count();

            return response()->json(['total_encounters_last_month' => $totalEncountersLastMonth]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function encountersByQuarter($year)
    {
        try {
            $quarters = [
                'Q1' => ['start' => Carbon::parse($year)->startOfYear()->startOfQuarter(), 'end' => Carbon::parse($year)->startOfYear()->endOfQuarter()],
                'Q2' => ['start' => Carbon::parse($year)->startOfYear()->addQuarters(1)->startOfQuarter(), 'end' => Carbon::parse($year)->startOfYear()->addQuarters(1)->endOfQuarter()],
                'Q3' => ['start' => Carbon::parse($year)->startOfYear()->addQuarters(2)->startOfQuarter(), 'end' => Carbon::parse($year)->startOfYear()->addQuarters(2)->endOfQuarter()],
                'Q4' => ['start' => Carbon::parse($year)->startOfYear()->addQuarters(3)->startOfQuarter(), 'end' => Carbon::parse($year)->startOfYear()->addQuarters(3)->endOfQuarter()],
            ];

            $totalEncountersByQuarter = [];
            foreach ($quarters as $quarter => $dates) {
                $totalEncountersByQuarter[$quarter] = EnroleeVisit::whereBetween('date_of_visit', [$dates['start']->toDateString(), $dates['end']->toDateString()])->count();
            }

            return response()->json(['total_encounters_' . $year . '_by_quarter' => $totalEncountersByQuarter]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function test()
    {
        //$response = Capitation::with('groups')->get();
        $response = CapitationGroup::join('capitations as c','c.group_id','capitation_grouping.id')
        ->select('name','cap_year','c.id')
        ->where('c.provider_id',177)->get();
        return response()->json($response);
    }

    public function lgas(){
        $lgas = LGA::with('wards')->get();
        return response()->json([$lgas], 200);
    }

    public function ExecutiveAnalysis(Request $request)
    {
        try {
            $dateRange = $request->input('dateRange');
            if(empty($dateRange)){
                //$dateRange[0] = Carbon::now()->startOfMonth()->toDateString();
                //$dateRange[1] = Carbon::now()->endOfMonth()->toDateString();
                $dateRange[0] = Carbon::parse('1995-01-01')->format('Y-m-d');
                $dateRange[1] = Carbon::now()->format('Y-m-d');
            }else{
                $dateRange[0] = Carbon::parse($dateRange[0])->format('Y-m-d');
                $dateRange[1] = Carbon::parse($dateRange[1])->format('Y-m-d');
            }            
            // Counting distinct enrollees who have visits
            // If mode_of_enrolment is in enrolee_visits
            $external_db  = env('EX_DB_DATABASE');
            $internal_db  = env('DB_DATABASE');
            $sql = "SELECT SUM(total_cap) as total from $external_db.capitations c JOIN $external_db.capitation_grouping g ON g.id = c.group_id WHERE CONCAT(g.cap_year,'-',LPAD(g.month,2,'0'),'-','01') BETWEEN '$dateRange[0]' AND '$dateRange[1]'";
            $capitation = floatval(DB::select(DB::raw($sql))[0]->total ??0);
            $totalEnrolleesAll = Enrolee::count();


            $EnrolleeByScheme = Enrolee::selectRaw('COUNT(mode_of_enrolment) AS total, mode_of_enrolment')
            ->groupBy('mode_of_enrolment')
            ->whereBetween('synced_datetime',$dateRange)
            ->get();

            $EnrolleeByVulnerabilityStatus = Enrolee::selectRaw('COUNT(vulnerability_status) AS total, vulnerability_status')
            ->groupBy('vulnerability_status')
            ->whereBetween('synced_datetime',$dateRange)
            ->get()->pluck('total','vulnerability_status');

            $EnrolleeByOccupation = Enrolee::selectRaw('COUNT(occupation) AS total, occupation')
            ->groupBy('occupation')
            ->whereBetween('synced_datetime',$dateRange)->orderBy('total', 'desc')
            ->get()->pluck('total','occupation');

            $EnrolleeBySex = Enrolee::selectRaw('COUNT(occupation) AS total, sex')
            ->groupBy('sex')
            ->whereBetween('synced_datetime',$dateRange)
            ->get()->pluck('sex', 'total');

            $EnrolleeByZone = DB::select(DB::raw("SELECT z.zone, COUNT(z.zone) as total FROM $external_db.tbl_enrolee e JOIN $external_db.lga on lga.id = e.lga JOIN $external_db.tbl_zones z on z.id = lga.zone GROUP BY z.zone"));

            $EnrolleeByScheme->map(function($item){
                if($item->mode_of_enrolment == 'huwe'){
                    $item->mode_of_enrolment = 'BHCPF';
                    return $item;
                }
                if($item->mode_of_enrolment == 'Premium' ){
                    $item->mode_of_enrolment = 'Informal';
                    return $item;
                }
                return $item;
            });



            $visitsGroupedByFunding = DB::table( $internal_db.'.enrolee_visits')
            ->join($external_db.'.tbl_enrolee', 'enrolee_visits.nicare_id', '=', $external_db.'.tbl_enrolee.enrolment_number')
            ->select($external_db.'.tbl_enrolee.mode_of_enrolment', DB::raw('COUNT(*) as total_visits'))
            ->groupBy($external_db.'.tbl_enrolee.mode_of_enrolment')
            ->whereBetween('enrolee_visits.date_of_visit',$dateRange)
            ->get();
            
            $visitsGroupedBySex =EnroleeVisit::select('sex', DB::raw('COUNT(*) as total_visits'))
            ->groupBy('sex')->whereBetween('date_of_visit',$dateRange)
            ->get()->pluck('sex','total_visits');

            $totalEnrollees = EnroleeVisit::whereBetween('enrolee_visits.date_of_visit',$dateRange)->get();                    
            $totalDistinctEnrollees = $totalEnrollees->unique('nicare_id')->count();
            $totalReferrals = EnroleeVisit::where('referred', 'yes')
                    ->whereBetween('enrolee_visits.date_of_visit',$dateRange)
                    ->count();
    
            $top10Services = EnroleeVisit::select('service_accessed',DB::raw('COUNT(*) as total'))
            ->whereBetween('date_of_visit', [$dateRange[0], $dateRange[1]])
            ->groupBy('service_accessed')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->get()->pluck('total','service');  
            
            $top10Facilities = EnroleeVisit::select('facility_id',DB::raw('COUNT(*) as total'))
                ->whereBetween('date_of_visit', [$dateRange[0], $dateRange[1]])
                ->groupBy('facility_id')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(10)
                ->get()->pluck('total','facility');  

            $top10Wards = EnroleeVisit::select('ward',DB::raw('COUNT(*) as total'))
                ->whereBetween('date_of_visit', [$dateRange[0], $dateRange[1]])
                ->groupBy('ward')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(10)
                ->get()->pluck('total','ward_name');  

            $visitsGroupedByFunding->map(function($item){
                if($item->mode_of_enrolment == 'huwe'){
                    $item->mode_of_enrolment = 'BHCPF';
                    return $item;
                }
                if($item->mode_of_enrolment == 'premium'){
                    $item->mode_of_enrolment = 'NGSCHS';
                    return $item;
                }
                return $item;
            });

            return response()->json([
                "enrollee_by_scheme"=>$EnrolleeByScheme,
                'visits_by_mode_of_enrolment' => $visitsGroupedByFunding,
                'vulnerability_status'=>$EnrolleeByVulnerabilityStatus,
                'occupations'=>$EnrolleeByOccupation,
                'sex' =>$EnrolleeBySex,
                "enrollee_by_zone"=>$EnrolleeByZone,
                'total_visits' => $totalEnrollees->count(),
                'total_distinct_visits' => $totalDistinctEnrollees,
                'visits_by_sex' => $visitsGroupedBySex,
                'total_referrals'=> $totalReferrals,
                'top_accessed' => $top10Services,
                'top_facility' => $top10Facilities,
                'top_wards' =>$top10Wards,
                "totalEnrolleesAll"=>number_format($totalEnrolleesAll),
                'capitation'=>$capitation

            ]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }

    }
}
