<?php

namespace App\Http\Controllers\v1;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use DB;

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
     * 商用電源の価格を登録する.
     *
     * @return void
     */
    public function recordGridPrice(Request $request)
    {
        try {
            $ret = [];
            $params = $request->all();
            try {
                $contents = $params['contents'];
                $json = json_decode($contents);
                foreach ($json as $item) {
                    $text = $item->text;
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
}
