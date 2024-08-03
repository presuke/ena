<?php

namespace App\Service;

use DB;

class Scene
{
  public static function vote(&$ret, $room, &$players)
  {
    try {
      $players = $players->toArray();

      $playerNum = 0;
      for ($i = 0; $i < count($players); $i++) {
        $player = (array)$players[$i];
        $player['votenum'] = 0;
        $players[$i] = $player;
        if ($player['flgDead'] == 0) {
          $playerNum++;
        }
      }
      $votes = DB::table('history')->select('playerid', 'targetid', 'action', 'upd')->where(
        [
          'roomid' => $room->id,
          'day' => $room->day,
          'action' => 'vote',
          'time' => 0,
        ]
      )->get();

      $voteResults = [];
      for ($i = 0; $i < count($players); $i++) {
        $player = $players[$i];
        $player['votenum'] = $votes->where('targetid', $player['id'])->count();
        $players[$i] = $player;
        $voteResults[$player['id']] = $player['votenum'];
      }
      if ($votes->count() < $playerNum) {
        $ret['votes'] = $votes;
        $ret['info']['message'] = '投票の時間です。誰かに投票してください。';
      } else {
        $topPlayers = [];
        foreach ($voteResults as $playerid => $result) {
          if ($result == max($voteResults)) {
            $topPlayers[] = $playerid;
          }
        }

        if (count($topPlayers) == 1) {
          $timelimit = 30 - (strtotime(now()) - strtotime($votes->max('upd')));
          if ($timelimit > 0) {
            //$ret['info']['message'] = $timelimit . '秒後に投票を締め切ります。変更するならお早めに。';
            $ret['info']['countdown']['sec'] = $timelimit;
            $ret['info']['countdown']['action'] = '投票';
          } else {
            try {
              DB::beginTransaction();
              $action = DB::table('history')->where(
                [
                  'roomid' => $room->id,
                  'playerid' => 0,
                  'day' => $room->day,
                  'time' => 0,
                  'action' => 'punish',
                ]
              );
              if ($action->count() == 0) {
                DB::table('history')->insert(
                  [
                    'roomid' => $room->id,
                    'playerid' => 0,
                    'targetid' => $topPlayers[0],
                    'day' => $room->day,
                    'time' => 0,
                    'action' => 'punish',
                  ],
                  ['playerid', 'day', 'time', 'action']
                );

                DB::table('player')->where(['id' => $topPlayers[0], 'roomid' => $room->id])->update(['flgDead' => 1]);

                DB::table('room')->where(['id' => $room->id])->update(['time' => 1]);
              }
              DB::commit();
            } catch (\Exception $ex) {
              $ret['code'] = 99;
              $ret['error'] = $ex->getMessage();
            }
            $ret['info']['message'] = '投票を締め切りました。判定をお待ちください・・。';
            $ret['info']['time'] = 0;
          }
        } else {
          $ret['info']['message'] = 'トップが複数います。どなたか、投票先を変えてください。';
        }
      }
    } catch (\Exception $ex) {
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
  }

  public static function voteResult(&$ret, $room, $players, $me)
  {
    try {
      $result = DB::table('history')->where(
        [
          'roomid' => $room->id,
          'playerid' => 0,
          'day' => $room->day,
          'time' => 0,
          'action' => 'punish',
        ]
      )->first();

      $ret['info']['result']['vote']['targetid'] = $result->targetid;

      $confirms = DB::table('history')->where(
        [
          'roomid' => $room->id,
          'day' => $room->day,
          'time' => 1,
          'action' => 'confirmVoteResult',
        ]
      )->get();
      $ret['confirms'] = $confirms;

      if ($confirms->whereIn('playerid', $me->id)->count() == 0) {
        $ret['info']['result']['vote']['confirmed'] = false;
      } else {
        $ret['info']['result']['vote']['confirmed'] = true;
        $cntConfirmed = $confirms->count();
        $cntLives = $players->whereIn('flgDead', 0)->count();
        if ($cntConfirmed < $cntLives) {
          $ret['info']['message'] = $cntLives . '人中' . ($cntLives - $cntConfirmed) . '人が投票結果を未確認です。もうちょっと待ちましょう。';
        } else {
          self::jadge($ret, $room);
        }
      }
    } catch (\Exception $ex) {
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
  }

  public static function action(&$ret, $room, &$players, $me)
  {
    try {
      $playerNum = $players->whereIn('flgDead', 0)->count();
      $acted = DB::table('history')->select('playerid')->where([
        'roomid' => $room->id,
        'day' => $room->day,
        'time' => $room->time,
      ]);

      if ($me->roleid == 1) {
        $attackedPlayers = DB::table('history')->select('targetid',)->where(
          [
            'roomid' => $room->id,
            'day' => $room->day,
            'time' => $room->time,
            'action' => 'attack',
          ]
        )->get();
        foreach ($players as $player) {
          foreach ($attackedPlayers as $attackedPlayer) {
            if ($player->id == $attackedPlayer->targetid) {
              $player->attacked = 9;
            }
          }
        }
      }
      $ret['acted'] = $acted->get();
      //生きてる人と行動済みの人を比べる
      if ($playerNum == $acted->count()) {
        $timelimit = 10 - (strtotime(now()) - strtotime($acted->max('upd')));
        if ($timelimit > 0) {
          //$ret['info']['message'] = $timelimit . '秒後に夜が明けます。結果は朝知ることになるでしょう・・・';
          $ret['info']['countdown']['sec'] = $timelimit;
          $ret['info']['countdown']['action'] = '行動';
        } else {
          try {
            $actions = DB::table('history')->where(
              [
                'roomid' => $room->id,
                'day' => $room->day,
                'time' => 2,
              ]
            )->get();

            $attacked = $actions->whereIn('action', 'attack');
            $saved = $actions->whereIn('action', 'save');
            $considers = $actions->whereIn('action', 'consider');
            $changes = $actions->whereIn('action', 'change');
            $freedoms = $actions->whereIn('action', 'freedom');

            $result = [];
            $cntSave = 0;
            $cntConsider = 0;
            foreach ($attacked as $attack) {
              foreach ($saved as $save) {
                if ($attack->targetid == $save->targetid) {
                  $attack->targetid = 0;
                  $cntSave++;
                }
              }
              foreach ($considers as $consider) {
                if ($attack->targetid == $consider->playerid) {
                  $attack->targetid = 0;
                  $cntConsider++;
                }
              }
            }
            $result['cntSave'] = $cntSave;
            $result['cntConsider'] = $cntConsider;
            $result['cntChange'] = $changes->count();
            $result['attacked'] = [];
            $result['freedom'] = [];
            DB::beginTransaction();
            foreach ($attacked as $attack) {
              if ($attack->targetid > 0) {
                $result['attacked'][] = $attack->targetid;
                DB::table('player')->where(['id' => $attack->targetid, 'roomid' => $room->id])->update(['flgDead' => 1]);
              }
            }
            foreach ($freedoms as $freedom) {
              if ($freedom->targetid > 0) {
                $result['freedom'][] = $freedom->targetid;
                DB::table('player')->where(['id' => $freedom->targetid, 'roomid' => $room->id])->update(['flgDead' => 0]);
              }
            }
            foreach ($changes as $change) {
              $a = DB::table('player')->where(['id' => $change->playerid])->first();
              $b = DB::table('player')->where(['id' => $change->targetid])->first();
              DB::table('player')->where(['id' => $a->id])->update(['role' => $b->role]);
              DB::table('player')->where(['id' => $b->id])->update(['role' => $a->role]);
            }
            DB::table('history')->insert([
              'roomid' => $room->id,
              'playerid' => 0,
              'targetid' => 0,
              'day' => $room->day,
              'time' => 2,
              'action' => json_encode($result),
            ]);
            $room = DB::table('room')->where(['id' => $room->id, 'time' => 2])->first();
            DB::table('room')->where(['id' => $room->id])->update(['day' => $room->day + 1, 'time' => 3]);
            DB::commit();
          } catch (\Exception $ex) {
            $ret['code'] = 99;
            $ret['error'] = $ex->getMessage();
          }
        }
      }
    } catch (\Exception $ex) {
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
  }

  public static function actionResult(&$ret, $room, $players, $me)
  {
    try {
      $confirms = DB::table('history')->where(
        [
          'roomid' => $room->id,
          'day' => $room->day,
          'time' => 3,
          'action' => 'confirmActionResult',
        ]
      )->get();

      $action = DB::table('history')->select('action')->where(
        [
          'roomid' => $room->id,
          'playerid' => 0,
          'day' => $room->day - 1,
          'time' => 2,
        ]
      )->first();

      $result = json_decode($action->action);

      $ret['info']['result']['action'] = $result;

      $ret['info']['result']['confirmed'] = $confirms;

      //自分が確認したかどうか
      $ret['info']['result']['myconfirmed'] = ($confirms->whereIn('playerid', $me->id)->count() > 0);

      if ($confirms->whereIn('playerid', $me->id)->count() > 0) {
        //生きてるプレイヤーが全員確認したかどうか
        $lstLive = [];
        $lstConfirm = [];
        foreach ($players as $player) {
          if ($player->flgDead == 0) {
            $lstLive[] = $player->id;
          }
        }
        foreach ($confirms as $confirm) {
          if (in_array($confirm->playerid, $lstLive)) {
            $lstConfirm[] = $confirm->playerid;
          }
        }
        if (count($lstConfirm) < count($lstLive)) {
          $ret['info']['message'] = count($lstLive) . '人中' . (count($lstLive) - count($lstConfirm)) . '人が結果を未確認です。もうちょっと待ちましょう。';
        } else {
          self::jadge($ret, $room);
        }
      }
    } catch (\Exception $ex) {
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
  }

  private static function jadge(&$ret, $room)
  {
    try {
      $players = DB::table('player')->select(
        'id',
        'roomid',
        'name',
        'sex',
        'img',
        'role',
        'flgDead',
      )->where(['roomid' => $room->id,])->orderBy('id')->get();

      //残プレイヤーの構成を確認
      $cntJinro = 0;
      $cntTeamJinro = 0;
      $cntTeamPeople = 0;
      foreach ($players as $player) {
        if ($player->flgDead == 0) {
          if ($player->role == 1) {
            $cntJinro++;
          }
          if ($player->role == 1 || $player->role == 5 || $player->role == 7) {
            $cntTeamJinro++;
          } else {
            $cntTeamPeople++;
          }
        }
      }

      $win = 0;
      if ($cntTeamJinro >= $cntTeamPeople) {
        $win = 1;
      } else if ($cntJinro == 0) {
        $win = 2;
      }

      if ($win == 0) {
        //決着がつかなければ時を進める
        $timeNext = $room->time == 1 ? 2 : 0;
        DB::table('room')->where(['id' => $room->id])->update(['time' => $timeNext]);
      } else {
        //決着がついたら昼にする
        DB::table('room')->where(['id' => $room->id])->update(['time' => 4, 'win' => $win]);
      }
    } catch (\Exception $ex) {
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
  }
}
