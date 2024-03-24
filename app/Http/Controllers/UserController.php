<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Transformers\UtilResource;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(protected UserService $userService)
    {
        
    }

    public function index()
    {
        try {            
            $users = $this->userService->getAllUsers();
            return new UtilResource($users, false, 200);
        } catch (\Exception $e) {
            return new UtilResource($e->getMessage(), true, 400);
        }
    }

    public function show($id)
    {
        try {
            $user = $this->userService->getUserById($id);
            return new UtilResource($user, false, 200);
        } catch (\Exception $e) {
            return new UtilResource($e->getMessage(), true, 400);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:parents',
                'password' => 'required|confirmed',
                'password_confirmation' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',              
                'phone_number' => 'required'
            ]);

            $data = $request->all();
            $user = $this->userService->createUser($data);

            return new UtilResource($user, false, 200);
        } catch (ValidationException $e) {
            return new UtilResource($e->errors(), true, 400);
        } catch (\Exception $e) {
            return new UtilResource($e->getMessage(), true, 400);
        }
    }

    public function assignRole(Request $request){
        try{
    
            $user = User::find($request->get('model_id'));            
            if($request->get('type')=='assign'){
                $user->assignRole($request->get('role'));
            }else{                
                $user->removeRole($request->get('role'));
            }
            return response('Updated Successfully',200);
        }catch(\Exception $e){
            if(env('APP_DEBUG')){
                return response($e->getMessage(),400);                
            }else{
                return response($e->getMessage(),400);
            }
        }
    }

    public function assignPermission(Request $request){
        try{
    
            $user = User::find($request->get('model_id'));            
            if($request->get('type')=='assign'){
                $user->givePermissionTo($request->get('permission'));
            }else{
                $user->revokePermissionTo($request->get('permission'));
            }
            return response('Updated Successfully',200);
        }catch(\Exception $e){
            if(env('APP_DEBUG')){
                return response($e->getMessage(),400);                
            }else{
                return response('failed',400);
            }
        }
    }

}
