<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use DB;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/authed';

    protected function redirectTo()
    {
        $user = Auth::user();
        if (!$user) {
            return '/';
        }
        $id = $user->email;
        $time = date('Y-m-d H:i:s');
        $token = md5($id . $time);
        $data = ['name' => $id, 'tokenable_type' => '', 'tokenable_id' => 0, 'created_at' => $time, 'token' => $token];
        DB::table('personal_access_tokens')->insert($data);
        return '/authed?' . $token;
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
