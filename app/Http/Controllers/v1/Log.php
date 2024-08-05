<?php

namespace App\Http\Controllers\v1;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Service\Action;
use App\Service\Scene;
use App\Http\Controllers\Auth\TokenController;
use DB;

class Log extends BaseController
{
    /**
     * サーバタイムを返す
     *
     * @param Request $request
     * @return void
     */
    public function getServerTime(Request $request)
    {
        try {
            $ret = [];
            $ret['code'] = 0;
            $ret['time'] = time();
        } catch (\Exception $ex) {
            $ret['code'] = 99;
            $ret['errors'][] = $ex;
        }
        return response()->json($ret);
    }

    /**
     * ハイブリッドインバータのデータを保存する
     *
     * @param Request $request
     * @return void
     */
    public function write(Request $request)
    {
        try {
            $ret = [];
            $ret['code'] = 0;
            $params = $request->all();
            $user = $params['user'];
            $datas = $params['datas'];

            DB::beginTransaction();
            try {
                foreach ($datas as $data) {
                    $data['user'] = $user['id'];
                    $data['no'] = $user['no'];
                    $data['create_at'] = date('Y-m-d H:i:s', $data['create_at']);

                    $where = [
                        'user' => $data['user'],
                        'no' => $data['no'],
                        'create_at' => $data['create_at'],
                    ];
                    $record = DB::table('hidata')->where($where);
                    if ($record->count() == 0) {
                        DB::table('hidata')->insert($data);
                    } else {
                        $record->update($data);
                    }
                }
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                $ret['code'] = 98;
                $ret['errors'][] = $ex;
            }
        } catch (\Exception $ex) {
            $ret['code'] = 99;
            $ret['errors'][] = $ex;
        }
        return response()->json($ret);
    }

    /**
     * ユーザが保有しているハイブリッドインバータのNo一覧を取得する
     *
     * @param Request $request
     * @return void
     */
    public function getMyHybridInverterNumbers(Request $request)
    {
        try {
            $params = $request->query();
            $token = TokenController::getTokenInfo($params['token']);
            $datas = DB::table('hidata')->select('no')->where(['user' => $token->name]);
            $data = $datas->groupBy('no')->get();
            $ret['code'] = 0;
            $ret['data'] = $data;
        } catch (\Exception $ex) {
            $ret['code'] = 99;
            $ret['errors'][] = $ex;
        }
        return response()->json($ret);
    }

    /**
     * ユーザが保有しているハイブリッドインバータのデータを取得する
     *
     * @param Request $request
     * @return void
     */
    public function getHybridInverterDatas(Request $request)
    {
        try {
            $params = $request->query();
            $token = TokenController::getTokenInfo($params['token']);
            $sql = "SELECT * FROM hidata WHERE user='" . $token->name . "' AND no ='" . $params['no'] . "' AND create_at BETWEEN '" . $params['date'] . " 00:00:00' AND '" . $params['date'] . " 23:59:59' ORDER BY create_at ASC";
            $data = DB::select($sql);
            $ret['code'] = 0;
            $ret['data'] = $data;
        } catch (\Exception $ex) {
            $ret['code'] = 99;
            $ret['errors'][] = $ex;
        }
        return response()->json($ret);
    }
}
