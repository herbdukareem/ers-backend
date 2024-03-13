<?php

namespace App\Http\Controllers;

use App\Jobs\SendMail as JobsSendMail;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\SendMail;
use App\Models\ActivatedUser;
use App\Models\CapitationGroup;
use App\Models\Enrolee;
use App\Models\EnroleeVisit;
use App\Models\Service;
use App\Services\UserService;
use App\Transformers\UtilResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use phpseclib3\Crypt\AES;

class AuthController
{
    protected $responseBody;

    public function __construct(protected UserService $userService)
    {
      
    }

    
    public function login(Request $request)
    {
    
        try{    
              
            $request->validate([
                'activation_code' => 'required',
                'password' => 'required',
            ]);
            
            $user = User::where(['nicare_code'=> $request->activation_code, 'password'=>md5($request->get('password'))])->first();
            //return !Hash::check($request->get('password'), $user?->password);
            if (!$user) {
                throw new \Exception('Incorrect credentials', 401);
            }    
            $services = Service::where('level_of_care','Primary')->get();
            $enrolees = Enrolee::select('id','enrolee_type','surname','first_name','other_name','enrolment_number','phone_number','sex','lga','ward','provider_id')
                        ->where(['lga'=> $user->lga, 'ward'=>$user->ward, 'provider_id'=>$user->provider_id])->get();
            $accessToken = $user->createToken('AuthToken')->accessToken;            

            $previous_month = Carbon::now()->subMonth();
            $enroleesVisit = EnroleeVisit::where('reporting_month', $previous_month)->get();
            $capitations = CapitationGroup::join('capitations as c','c.group_id','capitation_grouping.id')
            ->select('name','cap_year','c.id','month')
            ->where('c.provider_id',$user->provider_id)->get();
            return new UtilResource(["services"=> $services, "user" => $user,'enrolees'=>$enrolees, 'capitations'=>$capitations, "accessToken" => $accessToken, "live"=>$enroleesVisit ], false, 200);       
        }catch (ValidationException $e) {
            return new UtilResource($e->errors(), true, 400);
        } catch (\Exception $e) {
            return new UtilResource($e->getMessage(), true, 400);
        }
        
    }

    public function enrolees($id){
        try {
            $services = Service::where('level_of_care','Primary')->get();
            $user =  User::find($id);
            $capitations = CapitationGroup::join('capitations as c','c.group_id','capitation_grouping.id')
            ->select('name','cap_year','c.id','month')
            ->where('c.provider_id',$user->provider_id)->get();
            $enrolees = Enrolee::select('id','enrolee_type','surname','first_name','other_name','enrolment_number','phone_number','sex','lga','ward','provider_id')
            ->where(['lga'=> $user->lga, 'ward'=>$user->ward, 'provider_id'=>$user->provider_id,'capitations'=>$capitations])->get();
            return new UtilResource(["services"=> $services, "user" => $user,'enrolees'=>$enrolees,  ], false, 200);       
        }catch (ValidationException $e) {
            return new UtilResource($e->errors(), true, 400);
        } catch (\Exception $e) {
            return new UtilResource($e->getMessage(), true, 400);
        }
    }
}

