<?php

namespace App\Http\Controllers\v1;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Service\Auth;
use App\Service\Action;
use App\Service\Scene;
use DB;

class Play extends BaseController
{

    public function action(Request $request)
    {
        try {
            $ret = [];
            $ret['code'] = 0;
            $params = $request->all();
            $params = $params['params'];
            try {
                $myid = $params['myid'];
                $action = $params['action'];
                $authToken = $params['authtoken'];

                $checkStatus = Auth::checkAcuthToken($myid, $authToken);
                if ($checkStatus == 0) {
                    //自プレイヤー
                    $me = DB::table('player')->select('id', 'roomid', 'role')->where(
                        [
                            'id' => $myid,
                        ]
                    )->first();

                    //自ルーム
                    $room = DB::table('room')->select('id', 'day', 'time')->where(
                        [
                            'id' => $me->roomid,
                        ]
                    )->first();

                    /*
                    //重複チェック
                    $exists = DB::table('history')->select('id', 'day', 'time')->where(
                        [
                            'roomid' => $room->id,
                            'playerid' => $me->id,
                            'day' => $room->day,
                            'time' => $room->time,
                        ]
                    )->count();
                    */

                    switch ($action) {
                        case 'vote': {
                                $targetid = $params['targetid'];
                                $ret = Action::vote($me, $room, $targetid);
                                break;
                            }

                        case 'confirmVoteResult': {
                                $param = [];
                                $param['action'] = 'confirmVoteResult';
                                $param['message'] = '投票結果確認';
                                $ret = Action::confirm($me, $room, $param);
                                break;
                            }

                        case 'confirmActionResult': {
                                $param = [];
                                $param['action'] = 'confirmActionResult';
                                $param['message'] = '襲撃結果確認';
                                $ret = Action::confirm($me, $room, $param);
                                break;
                            }

                        case 'sleep': {
                                $ret = Action::sleep($me, $room);
                                break;
                            }

                        case 'attack': {
                                $targetid = $params['targetid'];
                                $ret = Action::attack($me, $room, $targetid);
                                break;
                            }

                        case 'save': {
                                $targetid = $params['targetid'];
                                $ret = Action::save($me, $room, $targetid);
                                break;
                            }

                        case 'predict': {
                                $targetid = $params['targetid'];
                                $ret = Action::predict($me, $room, $targetid);
                                break;
                            }

                        case 'expose': {
                                $targetid = $params['targetid'];
                                $ret = Action::expose($me, $room, $targetid);
                                break;
                            }

                        case 'change': {
                                $targetid = $params['targetid'];
                                $ret = Action::change($me, $room, $targetid);
                                break;
                            }

                        case 'consider': {
                                $targetid = $params['targetid'];
                                $ret = Action::consider($me, $room, $targetid);
                                break;
                            }

                        case 'freedom': {
                                $targetid = $params['targetid'];
                                $ret = Action::freedom($me, $room, $targetid);
                                break;
                            }

                        default: {
                                $ret['error'] = 'undefined action[' . $action . ']';
                                break;
                            }
                    }
                } else {
                    $ret['code'] = $checkStatus;
                }
            } catch (\Exception $e) {
                $ret['code'] = 99;
                $ret['errors'][] = $e;
            }
        } catch (\Exception $ex) {
            $ret['code'] = 99;
            $ret['errors'][] = $ex;
        }
        return response()->json($ret);
    }

    /**
     * 現状の部屋の状態.
     *
     * @param Request $request
     * @return void
     */
    public function getRoomStatus(Request $request)
    {
        try {
            $ret = [];
            $ret['code'] = 0;
            $params = $request->all();
            try {
                $playerid = $params['playerid'];

                if (isset($params['authtoken'])) {
                    $authToken = $params['authtoken'];
                } else {
                    $authToken = '';
                }

                $checkStatus = Auth::checkAcuthToken($playerid, $authToken);
                if ($checkStatus == 0) {

                    //自プレイヤー
                    $me = DB::table('player')->select('id', 'roomid', 'name', 'sex', 'img', 'role as roleid', 'flgDead')->where(
                        [
                            'id' => $playerid,
                        ]
                    )->first();

                    //自分名無しエラー
                    if ($me->name == '' || $me->name == null) {
                        $ret['code'] = 8;
                    } else {

                        //自ルーム
                        $room = DB::table('room')->where(
                            [
                                'id' => $me->roomid,
                            ]
                        )->first();
                        $room->roles = json_decode($room->roles);

                        //自ルームのプレイヤー
                        $query = DB::table('player')->select(
                            'id',
                            'roomid',
                            'name',
                            'sex',
                            'img',
                            'flgDead',
                            'flgDead as attacked',
                        )->where(['roomid' => $me->roomid,])->orderBy('id');
                        //神様と人狼・裏切者以外は情報を伏せる
                        if ($me->roleid == 6) {
                            //神は全員の役割を把握する
                            $players = $query->selectRaw('role as roleid')->get();
                        } else if ($me->roleid == 1) {
                            //人狼は人狼と裏切者と吸血鬼の役割を把握する
                            $players = $query->selectRaw("(CASE role WHEN 1 THEN 1 WHEN 5 THEN 5 WHEN 7 THEN 7 ELSE 0 END) AS roleid")->get();
                        } else if ($me->roleid == 5) {
                            //裏切者は人狼と裏切者の役割を把握する
                            $players = $query->selectRaw("(CASE role WHEN 1 THEN 1 WHEN 5 THEN 5 ELSE 0 END) AS roleid")->get();
                        } else {
                            $players = $query->get();
                        }

                        $ret['info']['time'] = $room->time;
                        foreach ($players as $player) {
                            if ($player->sex == '') {
                                $ret['info']['time'] = -1;
                            }
                        }

                        //各タイムゾーンについて、状況に応じて値を付加する
                        switch ($ret['info']['time']) {
                                //未入室者あり
                            case -1: {
                                    $ret['info']['message'] = '未入室の人がいます。もう少し待ちましょう。';
                                    break;
                                }
                                //投票中
                            case 0: {
                                    Scene::vote($ret, $room, $players);
                                    break;
                                }
                                //投票結果確認待ち中
                            case 1: {
                                    Scene::voteResult($ret, $room, $players, $me);
                                    break;
                                }
                                //行動結果確認待ち中
                            case 2: {
                                    Scene::action($ret, $room, $players, $me);
                                    break;
                                }
                                //行動結果確認待ち中
                            case 3: {
                                    Scene::actionResult($ret, $room, $players, $me);
                                    break;
                                }
                                //勝敗
                            case 4: {
                                    //Scene::gameset($ret, $room, $players);
                                    $players = DB::table('player')->select(
                                        'id',
                                        'roomid',
                                        'name',
                                        'sex',
                                        'img',
                                        'flgDead',
                                        'flgDead as attacked',
                                    )->selectRaw('role as roleid')->where(['roomid' => $room->id])->get();
                                    $ret['win'] = $room->win;
                                    break;
                                }
                        }

                        $ret['me'] = $me;
                        $ret['room'] = $room;
                        $ret['players'] = $players;
                        //$ret['playersDone'] = $playersDone;
                    }
                } else {
                    $ret['code'] = $checkStatus;
                }
            } catch (\Exception $ex) {
                $ret['code'] = 99;
                $ret['errors'][] = $ex;
            }
        } catch (\Exception $ex) {
            $ret['code'] = 99;
            $ret['errors'][] = $ex;
        }
        return response()->json($ret);
    }


    /**
     * ログイン.
     *
     * @param Request $request
     * @return void
     */
    public function login(Request $request)
    {
        try {
            $ret = [];
            $params = $request->all();
            try {
                $playerid = $params['playerid'];
                $player = DB::table('player')->where(
                    [
                        'id' => $playerid,
                    ]
                )->first();

                if ($player == null) {
                    $ret['code'] = 1;
                    $ret['error'] = '指定されたユーザは存在しません。';
                } else if ($player->pass == $params['pass']) {
                    $ret['code'] = 0;
                    $ret['token'] = Auth::getAuthToken($playerid);
                } else {
                    $ret['code'] = 1;
                    $ret['error'] = 'パスワードが違います。';
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
}
