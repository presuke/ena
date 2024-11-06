<?php

namespace App\Http\Controllers\v1;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\TokenController;
use DB;
use Carbon\Carbon;

class Regist extends BaseController
{
    /**
     * ハイブリッドインバータの設定変更要求をラズパイへ渡す.
     *
     * @return void
     */
    public function read(Request $request)
    {
        try {
            $ret = [];
            $params = $request->all();
            try {
                $user = $params['user']['id'];
                $no = $params['user']['no'];

                $regist = DB::table('regist')->where(
                    [
                        'user' => $user,
                        'no' => $no,
                    ]
                )->whereNull('done_at')->orderBy('create_at', 'asc')->first();
                $ret['code'] = 0;
                $ret['regist'] = $regist;
            } catch (\Exception $ex) {
                $ret['code'] = 9;
                $ret['error'] = $ex->getMessage();
            }
        } catch (\Exception $ex) {
            $ret['code'] = 9;
            $ret['error'] = $ex->getMessage();
        }
        return response()->json($ret);
    }

    /**
     * ハイブリッドインバータの設定変更結果をラズパイから受け取る.
     *
     * @return void
     */
    public function report(Request $request)
    {
        try {
            $ret = [];
            $params = $request->all();
            try {
                $param = $params['regist'];
                $user = $param['user'];
                $no = $param['no'];
                $create_at = $param['create_at'];
                $done_at = $params['done_at'];
                $val_request = $params['request'];
                $val_result = $params['result'];
                if ($val_request == $val_result) {
                    $result = 'success';
                } else {
                    $result = 'failed(request:' . $val_request . ', result:' . $val_result . ')';
                }
                $regist = DB::table('regist')->where(
                    [
                        'user' => $user,
                        'no' => $no,
                        'create_at' => $create_at
                    ]
                );
                if ($regist->count() > 0) {
                    $regist->update(['done_at' => date('Y-m-d H:i:s', $done_at), 'result' => $result]);
                }
                $ret['code'] = 0;
                $ret['regist'] = $regist;
            } catch (\Exception $ex) {
                $ret['code'] = 9;
                $ret['error'] = $ex->getMessage();
            }
        } catch (\Exception $ex) {
            $ret['code'] = 9;
            $ret['error'] = $ex->getMessage();
        }
        return response()->json($ret);
    }

    /**
     * looop電気の価格を登録する.
     *
     * @return void
     */
    public function recordGridPrice(Request $request)
    {
        try {
            $ret = [];
            $params = $request->all();

            try {
                $hour = date('G', $params['time']);
                if ($hour > 0 && $hour < 23) {
                    $area = intVal($params['area']);
                    $contents = $params['contents'];
                    DB::beginTransaction();
                    for ($idx = 0; $idx <= 2; $idx++) {
                        $data = $contents[$idx];
                        if (isset($data['price_data'])) {
                            $timezone = 0;
                            foreach ($data['price_data'] as $price) {
                                $date = date('Y-m-d', $params['time'] + (($idx - 1) * 24 * 3600));
                                $rec['date'] = $date;
                                $rec['kbn'] = $area;
                                $rec['timezone'] = $timezone;
                                $rec['price'] = $price;
                                $where = [
                                    'date' => $rec['date'],
                                    'kbn' => $rec['kbn'],
                                    'timezone' => $rec['timezone'],
                                ];
                                $record = DB::table('gridprice')->where($where);
                                if ($record->count() == 0) {
                                    DB::table('gridprice')->insert($rec);
                                } else {
                                    $record->update($rec);
                                }
                                $timezone++;
                            }
                        }
                    }
                    DB::commit();
                }
                $ret['code'] = 0;
            } catch (\Exception $ex) {
                $ret['code'] = 9;
                $ret['error'] = $ex->getMessage();
            }
        } catch (\Exception $ex) {
            $ret['code'] = 9;
            $ret['error'] = $ex->getMessage();
        }
        return response()->json($ret);
    }

    /**
     * looop電気の価格を取得する.
     *
     * @return void
     */
    public function getGridPrice(Request $request)
    {
        try {
            $ret = [];
            $params = $request->query();
            //データ
            $date = str_replace("/", "-", $params['date']);
            $sql = "SELECT * FROM gridprice WHERE kbn='" . $params['area'] . "' AND date='" . $date . "' ORDER BY timezone ASC";
            $data = DB::select($sql);
            $ret['code'] = 0;
            $ret['data'] = $data;
        } catch (\Exception $ex) {
            $ret['code'] = 9;
            $ret['error'] = $ex->getMessage();
        }
        return response()->json($ret);
    }

    /**
     * ハイブリッドインバータの電源設定を登録する.
     *
     * @return void
     */
    public function recordSettingHybridInverter(Request $request)
    {
        try {
            $ret = [];
            $params = $request->all();
            $ret['params'] = $params;
            $token = TokenController::getTokenInfo($params['token']);
            $now = Carbon::now('Asia/Tokyo');
            try {
                $rec = [];
                $rec['user'] = $token->name;
                $rec['no'] = $params['no'];
                $rec['regist'] = $params['regist'];
                $rec['value'] = json_encode($params['value']);
                $rec['result'] = '1';
                $rec['create_at'] = $now;
                $rec['done_at'] = null;
                $where = [
                    'user' => $rec['user'],
                    'no' => $rec['no'],
                ];
                $record = DB::table('regist')->where($where);
                if ($record->count() == 0) {
                    DB::table('regist')->insert($rec);
                } else {
                    $record->update($rec);
                }
                $ret['code'] = 0;
            } catch (\Exception $ex) {
                $ret['code'] = 9;
                $ret['error'] = $ex->getMessage();
            }
        } catch (\Exception $ex) {
            $ret['code'] = 9;
            $ret['error'] = $ex->getMessage();
        }
        return response()->json($ret);
    }
}
