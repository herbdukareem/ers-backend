<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\Capitation;
use App\Models\CapitationGroup;
use App\Models\Enrolee;
use App\Models\EnroleeFormal;
use App\Models\EnroleeVisit;
use App\Models\Lga;
use App\Models\MedicalBill;
use App\Models\Service;
use Carbon\Carbon;
//use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

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

            $newestFacilitiesEntries = EnroleeVisit::select('created_at','facility_id',DB::raw('COUNT(*) as total'))
                ->whereBetween('date_of_visit', [$dateRange[0], $dateRange[1]])
                ->groupBy('created_at')
                ->groupBy('facility_id')
                ->orderByRaw('created_at DESC')
                ->limit(10)
                ->get()->map(function ($entry) {
                    $createdAt = Carbon::parse($entry->created_at);
                    $now = Carbon::now();

                    $sinceAdded = $now->longAbsoluteDiffForHumans($createdAt);
                    if($sinceAdded != 'now'){
                        $sinceAdded .= ' ago';
                    }

                    $entry->since_added = $sinceAdded;
                    return $entry;
                  });

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
                'newest_facility_entries'=>$newestFacilitiesEntries,
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
        $lgas = Lga::with('wards')->get();
        return response()->json([$lgas], 200);
    }

    public function topAccessedService(Request $request)
    {
        extract($this->getChartRequestData($request));

        $selectAs = '';
        $groupBy = '';


        if (!empty($zone)) {
            $selectAs = DB::raw("IF(l.zone = 1, 'Zone A', IF(l.zone = 2,'Zone B', 'Zone C')) as name");
            $groupBy = "name";
        }

        if($queryBy == 'dateType'){
            switch ($dateType) {
                case 'days':
                    $groupBy = "DATE(date_of_visit)";
                    $selectAs = DB::raw("DATE(date_of_visit) as name");
                    break;
                case 'months':
                    $groupBy = "CONCAT(YEAR(date_of_visit), '-',MONTH(date_of_visit))";
                    $selectAs = DB::raw("CONCAT(YEAR(date_of_visit), '-',MONTH(date_of_visit)) as name");
                    break;
                case 'years':
                    $groupBy = "YEAR(date_of_visit)";
                    $selectAs = DB::raw("YEAR(date_of_visit) as name");
                    break;
                default:
                    break;
            }
        }else if($queryBy == 'facility'){
            $selectAs = DB::raw("p.hcpname as name");
            $groupBy = "p.hcpname";
        }else if($queryBy == 'ward'){
            $selectAs = DB::raw("w.ward as name");
            $groupBy = "w.ward";
        }

        $service_accessed_id = Service::where('case_name', $value)->value('id'); // Assuming you're only interested in the ID

        // Building the initial query
        $top10Services = EnroleeVisit::select('service_accessed', $selectAs, DB::raw('COUNT(*) as total'))
            ->when($service_accessed_id, function ($query) use ($service_accessed_id) {
                return $query->where('service_accessed', $service_accessed_id);
            })->whereBetween('date_of_visit', [$dateRange[0], $dateRange[1]]);

        // Applying location and zone filters
        $top10Services = $this->applyLocationFilters($top10Services, $location, $external_db,'enrolee_visits');
        $top10Services = $this->applyZoneFilter($zone, $top10Services);

        $top10Services = $top10Services->groupBy(DB::raw($groupBy))
                                    ->groupBy('service_accessed')
                                    ->groupBy('name')
                                    ->orderByRaw('name')->get();
        $transformedResult = $this->transformResults($top10Services, $dateType, $location, $zone);
        return response()->json($transformedResult);
    }

    public function enrolleeByCategory(Request $request)
    {
        extract($this->getChartRequestData($request));

        if($queryBy == 'dateType'){
            switch ($dateType) {
                case 'days':
                    $groupBy = "DATE(synced_datetime)";
                    $selectAs = DB::raw("DATE(synced_datetime) as name");
                    break;
                case 'months':
                    $groupBy = "CONCAT(YEAR(synced_datetime), '-',MONTH(synced_datetime))";
                    $selectAs = DB::raw("CONCAT(YEAR(synced_datetime), '-',MONTH(synced_datetime)) as name");
                    break;
                case 'years':
                    $groupBy = "YEAR(synced_datetime)";
                    $selectAs = DB::raw("YEAR(synced_datetime) as name");
                    break;
                default:
                    break;
            }
        }else if($queryBy == 'facility'){
            $selectAs = DB::raw("p.hcpname as name");
            $groupBy = "p.hcpname";
        }else if($queryBy == 'ward'){
            $selectAs = DB::raw("w.ward as name");
            $groupBy = "w.ward";
        }

        // Building the initial query
        $query = Enrolee::select(DB::raw('COUNT(vulnerability_status) AS total'),$selectAs)
                        ->whereBetween('synced_datetime',$dateRange);

        // Applying location and zone filters
        $query = $this->applyLocationFilters($query, $location, $external_db,'tbl_enrolee');
        $query = $this->applyZoneFilter($zone, $query);

        $result = $query->groupBy(DB::raw($groupBy))
                                    ->groupBy('name')
                                    ->orderByRaw('name')->get();
        $transformedResult = $result->map(function($item) use ($dateType, $location, $zone) {
                    $data = [
                        'total' => $item->total,
                    ];
                    if ($dateType && empty($location['lga_id']) && empty($zone)) {
                        $data['name'] = $this->dateResolver($item->name, $dateType);
                    } else {
                        $data['name'] = $item->name;
                    }
                    return $data;
                });
        return response()->json($transformedResult);
    }

    private function transformResults($top10Services, $dateType, $location, $zone) {
        return $top10Services->map(function($item) use ($dateType, $location, $zone) {
            $data = [
                'service' => $item->service,
                'total' => $item->total,
            ];
            if ($dateType && empty($location['lga_id']) && empty($zone)) {
                $data['name'] = $this->dateResolver($item->name, $dateType);
            } else {
                $data['name'] = $item->name;
            }
            return $data;
        });
    }

    private function getChartRequestData(Request $request) {
        $dateRange = $request->input('dateRange');
        $dateType = $request->input('dateType');
        $queryBy = $request->input('query_by');
        $value = $request->input('value');
        $dateRange = $this->getDateRange($dateRange);
        $location = $request->input('location');
        $location = [
            'lga_id'=> $location['lga']['id'] ?? null,
            'ward_id'=> $location['ward']['id'] ?? null
        ];

        $zone = $request->input('zone');
        $external_db  = env('EX_DB_DATABASE');

        return compact('dateRange', 'dateType', 'value', 'location', 'zone', 'external_db', 'queryBy');
    }

    private function applyLocationFilters($query, $location, $external_db, $table_name) {
        $query = $query->join("$external_db.lga as l", $table_name.'.lga', '=', 'l.id')
                    ->join("$external_db.ward as w", $table_name.'.ward', '=', 'w.id')
                    ->join("$external_db.tbl_providers as p", 'p.hcpward', '=', 'w.id');
        if (!empty($location['lga_id'])) {
            $query = $query->where('l.id', $location['lga_id']);
        }


        if (!empty($location['ward_id'])) {
            $query = $query->where('w.id', $location['ward_id']);
        }

        return $query;
    }

    private function applyZoneFilter($zone, $query) {
        if (!empty($zone)) {
            $query = $query->where('l.zone', $zone);
        }
        return $query;
    }

    private function dateResolver($date, $dateType){
        switch ($dateType) {
            case 'days':
                return Carbon::parse($date)->format('d M, Y');
                break;
            case 'months':
                return Carbon::parse($date)->format('M, Y');
                break;
            case 'years':
                return Carbon::parse($date)->format('Y');
                break;
            default:
                return Carbon::parse($date)->format('d M, Y');
        }
    }
    private function getDateRange($dateRange){
        if(empty($dateRange)){
            //$dateRange[0] = Carbon::now()->startOfMonth()->toDateString();
            //$dateRange[1] = Carbon::now()->endOfMonth()->toDateString();
            $dateRange[0] = Carbon::parse('1995-01-01')->format('Y-m-d');
            $dateRange[1] = Carbon::now()->format('Y-m-d');
        }else{
            $dateRange[0] = Carbon::parse($dateRange[0])->format('Y-m-d');
            $dateRange[1] = Carbon::parse($dateRange[1])->format('Y-m-d');
        }
        return $dateRange;
    }

    public function ExecutiveAnalysis(Request $request)
    {
        try {
            $dateRange = $request->input('dateRange');
            $dateRange = $this->getDateRange($dateRange);

            // Counting distinct enrollees who have visits
            // If mode_of_enrolment is in enrolee_visits
            $external_db  = env('EX_DB_DATABASE');
            $internal_db  = env('DB_DATABASE');
            $sql = "SELECT SUM(total_cap) as total from $external_db.capitations c JOIN $external_db.capitation_grouping g ON g.id = c.group_id WHERE CONCAT(g.cap_year,'-',LPAD(g.month,2,'0'),'-','01') BETWEEN '$dateRange[0]' AND '$dateRange[1]'";
            $capitation = floatval(DB::select(DB::raw($sql))[0]->total ??0);
            

            //bhfcpf : mode_of_enrolment= huwe and benefactor = 2
            /* gac : mode_of_enrolment= huwe and benefactor = 4
            gac : mode_of_enrolment= huwe and benefactor = 4 */        

            $EnrolleeByScheme = Enrolee::selectRaw("
                    SUM(CASE WHEN mode_of_enrolment LIKE 'Premium' THEN 1 ELSE 0 END) AS Informal,
                    SUM(CASE WHEN mode_of_enrolment LIKE 'huwe' AND funding NOT IN ('gac', 'cf', 'unicef') THEN 1 ELSE 0 END) AS BHCPF,
                    SUM(CASE WHEN mode_of_enrolment LIKE 'huwe' AND benefactor = 4 AND funding = 'gac' THEN 1 ELSE 0 END) AS GAC,
                    SUM(CASE WHEN mode_of_enrolment LIKE 'huwe' AND funding = 'cf' THEN 1 ELSE 0 END) AS Counterpart,
                    SUM(CASE WHEN mode_of_enrolment LIKE 'huwe' AND benefactor = 8 THEN 1 ELSE 0 END) AS UNICEF
                ")->whereBetween('synced_datetime',$dateRange)->where('status','1')
            ->get();
            $enrolleeFormalCount = EnroleeFormal::whereNotNull('lga')->where('status','1')->count();
            
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
            $schemes = [
                'Informal',
                'BHCPF',
                'Counterpart',
                'TISHIP',
                'GAC',
                'UNICEF'
            ];
            
            //$existingSchemes =array_keys(->toArray());
            $EnrolleeBySchemes = [];
            $EnrolleeBySchemes[] = [
                'total' => $enrolleeFormalCount,
                'mode_of_enrolment' => "Formal"
            ];
            $totalEnrolleesAll = $enrolleeFormalCount;
            foreach($EnrolleeByScheme->first()->toArray() as $key =>$value ){       
                $totalEnrolleesAll += (int) $value;
                if (in_array($key,$schemes)) {         
                    $EnrolleeBySchemes[] =[
                        'total' => $value,
                        'mode_of_enrolment' => $key
                    ];
                }
            }

            /* 
            $schemes->each(function ($scheme) use (&$EnrolleeByScheme, $existingSchemes) {
                if (!in_array($scheme, $existingSchemes)) {
                    $EnrolleeByScheme->push((object)[
                        'total' => 0,
                        'mode_of_enrolment' => $scheme,
                    ]);
                }
            }); */

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
                "enrollee_by_scheme"=>$EnrolleeBySchemes,
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

    public function accountAnalytics(Request $request){
        try{
            $sessionToken = $request->session()->getId();
            ApiToken::create(['_token'=>$sessionToken]);

            $api = env('ACC_API');
            $response = Http::withHeaders(['session_token' => $sessionToken])
            ->timeout(300)
            ->retry(3, 100)
            ->get($api . '/analytics',['session_token' => $sessionToken]);

            if ($response->status() == 200) {
                $data = $response->body();
            } else{
                throw new \Exception($response->reason());
            }

            return $data;
        }catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function accountRequest(Request $request){
        try{
            $sessionToken = $request->session()->getId();
            ApiToken::create(['_token'=>$sessionToken]);
            $data = $request->all();
            $data['session_token'] = $sessionToken;
            $api = env('ACC_API');
            $response = Http::withHeaders(['session_token' => $sessionToken])
                        ->timeout(30)
                        ->retry(3, 300)
                    ->{$request->method}($api . '/'. $request->route,$data);

            if ($response->status() == 200) {
                $data = $response->body();
            } else{
                throw new \Exception($response->reason());
            }

            return $data;
        }catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}
