<?php

namespace App\Http\Controllers\v1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use DB;

class Player extends BaseController
{
    /**
     * メンバー作成.
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {
        try {
            $ret = [];
            $params = $request->all();
            DB::beginTransaction();
            try {
                //同一名、同一アバターチェック
                $isDuplicate = false;
                $avators = DB::table('player')->select('name', 'sex', 'img')->where(
                    [
                        'roomid' => $params['roomid'],
                    ]
                )->get();
                foreach ($avators as $avator) {
                    if ($avator->name == $params['name']) {
                        $ret['code'] = 1;
                        $isDuplicate = true;
                        break;
                    } else if ($avator->sex == $params['sex'] && $avator->img == $params['img']) {
                        $ret['code'] = 2;
                        $isDuplicate = true;
                        break;
                    }
                }
                if ($isDuplicate) {
                    $ret['avators'] = $avators;
                } else {
                    $player = DB::table('player')->where(
                        [
                            'roomid' => $params['roomid'],
                            'id' => $params['id']
                        ]
                    );
                    $player->update(
                        [
                            'name' => $params['name'],
                            'sex' => $params['sex'],
                            'img' => $params['img'],
                            'pass' => $params['pass'],
                        ]
                    );
                    DB::commit();
                    $ret['player'] = $player->first();
                    $ret['msg'] = 'プレイヤーを作成しました！';
                }
            } catch (\Exception $e) {
                $ret['code'] = 9;
                $ret['error'] = $e;
                DB::rollback();
            }
        } catch (Exception $ex) {
        }
        return response()->json($ret);
    }
}
