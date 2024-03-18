<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nicare_code' => 'required|string',
            'password' => 'required|string',
        ]);

        // Convert the password input to an MD5 hash
        $passwordMd5 = md5($request->input('password'));

        // Attempt to find the user by their unique identifier and password
        $user = User::where('nicare_code', $request->input($this->username()))
                    ->where('password', $passwordMd5)
                    ->first();        
        if ($user) {
            // If the user is found, manually log in the user
            Auth::guard('web')->login($user, $request->filled('remember'));

            // Optional: Update session or perform other post-login actions

            return redirect()->intended($this->redirectPath());
        }        
        return back()->withErrors([
            "nicare_code"=>'invalid credentials'
        ]);
    }

    protected function redirectPath()
    {
        return $this->redirectTo;
    }

    public function username()
    {
        return 'nicare_code'; // Change this to the actual field you use for logins, e.g., 'email' or 'username'
    }
}
