<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use DB;

class TokenController extends Controller
{

    static public function getTokenInfo($token)
    {
        $ret = DB::table('personal_access_tokens')->where(['token' => $token])->first();
        return $ret;
    }
}
