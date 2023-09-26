<?php

namespace App\Http\Controllers;

use App\Jobs\SendMail as JobsSendMail;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\SendMail;
use App\Models\ActivatedUser;
use App\Models\Enrolee;
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
            $services = Service::all();
            $enrolees = Enrolee::select('id','enrolee_type','surname','first_name','other_name','enrolment_number','phone_number','sex','lga','ward','provider_id')
                        ->where(['lga'=> $user->lga, 'ward'=>$user->ward, 'provider_id'=>$user->provider_id])->get();
            $accessToken = $user->createToken('AuthToken')->accessToken;            
            return new UtilResource(["services"=> $services, "user" => $user,'enrolees'=>$enrolees, "accessToken" => $accessToken ], false, 200);       
        }catch (ValidationException $e) {
            return new UtilResource($e->errors(), true, 400);
        } catch (\Exception $e) {
            return new UtilResource($e->getMessage(), true, 400);
        }
        
    }
}

