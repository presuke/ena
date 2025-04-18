<?php

namespace App\Http\Controllers\v1;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Regist extends BaseController
{
    /**
     * ハイブリッドインバータの設定変更要求をラズパイへ渡す.
     *
     * @return void
     */
    public function readRegistSetting(Request $request)
    {
        try {
            $ret = [];
            $params = $request->all();
            try {
                $user = auth()->user();
                $now = Carbon::now('Asia/Tokyo');
                switch ($params['action']) {
                        //設定の読み込み
                    case 'get': {
                            $no = request('no');
                            $regists = DB::table('regist')->where(
                                [
                                    'user' => $user->email,
                                    'no' => $params['no'],
                                ]
                            )->orderBy('create_at', 'asc')->get();
                            $ret['code'] = 0;
                            $ret['regists'] = $regists;
                            $ret['now'] = $now->format('Y-m-d H:i:s');
                            break;
                        }
                        //設定完了の報告
                    case 'set': {
                            $result = $params['result'];
                            $regist = DB::table('regist')->where(
                                [
                                    'user' => $user->email,
                                    'no' => $params['no'],
                                    'mode' => $params['mode']
                                ]
                            );
                            if ($regist->count() > 0) {
                                $regist->update(['result' => $result, 'done_at' => $now]);
                            }
                            break;
                        }
                }
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
            $user = auth()->user();
            $now = Carbon::now('Asia/Tokyo');
            try {
                $rec = [];
                $rec['user'] = $user->email;
                $rec['no'] = $params['no'];
                $rec['mode'] = $params['mode'];
                $rec['regist'] = json_encode($params['regist']);
                $rec['result'] = '';
                $rec['create_at'] = $now;
                $rec['done_at'] = null;
                $where = [
                    'user' => $rec['user'],
                    'no' => $rec['no'],
                    'mode' => $rec['mode'],
                ];
                $record = DB::table('regist')->where($where);
                if ($params['flgDel'] == 0) {
                    if ($record->count() == 0) {
                        DB::table('regist')->insert($rec);
                    } else {
                        $record->update($rec);
                    }
                    $ret['message'] = '設定完了しました。';
                } else if ($params['flgDel'] == 1) {
                    $record->delete();
                    $ret['message'] = '設定削除完了しました。';
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
