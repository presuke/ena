<?php

namespace App\Service;

use Illuminate\Support\Facades\Crypt;
use App\Const\AuthConst;

class Auth
{
  public static function checkAcuthToken($playerid, $authToken)
  {
    try {
      $ret = 0;

      try {
        $token = Crypt::decrypt($authToken);
      } catch (\Exception $e) {
        $ret = 1;
        return $ret;
      }

      if (isset($token['playerid']) && isset($token['deadline'])) {
        if ($token['playerid'] == $playerid) {

          $now = date('Y/m/d H:i:s', strtotime('now'));
          $deadline = $token['deadline'];
          if ($deadline < $now) {
            $ret = 1;
          }
        } else {
          $ret = 9;
        }
      } else {
        $ret = 9;
      }
    } catch (Exception $ex) {
      $ret = 9;
    }
    return $ret;
    //code...
  }

  public static function getAuthToken($playerid)
  {
    $ret = '';
    try {
      $limit = 9;
      $token['playerid'] = $playerid;
      $token['deadline'] = date('Y/m/d H:i:s', strtotime('now +' . $limit . ' hours'));
      $ret = Crypt::encrypt($token);
    } catch (Exception $ex) {
    }
    return $ret;
  }
}
