<?php

namespace App\Service;

use DB;

class Action
{
  public static function vote($me, $room, $targetid)
  {
    $ret = [];
    try {

      DB::beginTransaction();
      //history
      $myAction = DB::table('history')->where(
        [
          'roomid' => $room->id,
          'playerid' => $me->id,
          'day' => $room->day,
          'time' => $room->time,
          'action' => 'vote',
        ]
      );
      if ($myAction->count() == 0) {
        DB::table('history')->insert([
          'roomid' => $room->id,
          'playerid' => $me->id,
          'day' => $room->day,
          'time' => $room->time,
          'action' => 'vote',
          'targetid' => $targetid,
        ]);
      } else {
        $myAction->update(['targetid' => $targetid]);
      }
      DB::commit();
      $ret['message'] = '投票完了しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function confirm($me, $room, $param)
  {
    $ret = [];
    try {

      DB::beginTransaction();
      //history
      $action = DB::table('history')->where(
        [
          'roomid' => $room->id,
          'playerid' => $me->id,
          'day' => $room->day,
          'time' => $room->time,
          'action' => $param['action'],
        ]
      );
      if ($action->count() == 0) {
        DB::table('history')->insert(
          [
            'roomid' => $room->id,
            'playerid' => $me->id,
            'targetid' => 0,
            'day' => $room->day,
            'time' => $room->time,
            'action' => $param['action'],
          ],
        );
      }
      DB::commit();
      $ret['message'] = $param['message'] . '完了しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function sleep($me, $room)
  {
    $ret = [];
    try {
      $actionName = 'sleep';
      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'day' => $room->day,
        'time' => $room->time,
      ];

      //history
      $action = DB::table('history')->where($myAction);
      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        $myAction['targetid'] = 0;
        DB::beginTransaction();
        DB::table('history')->insert($myAction);
        DB::commit();
      } else {
        DB::beginTransaction();
        $action->update(['action' => $actionName, 'targetid' => 0]);
        DB::commit();
      }
      $ret['message'] = '就寝しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function attack($me, $room, $targetid)
  {
    $ret = [];
    try {
      $actionName = 'attack';
      if ($me->role != 1) {
        $ret['error'] = 'あなたは人狼ではありません。';
        return $ret;
      }

      $target =  DB::table('player')->where(['id' => $targetid])->first();
      if ($target->flgDead != 0) {
        $ret['error'] = '投獄中のプレイヤーを襲撃することはできません。';
        return $ret;
      }


      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);
      DB::beginTransaction();
      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        $myAction['targetid'] = $targetid;
        DB::table('history')->insert($myAction);
      } else {
        $action->update(['action' => $actionName, 'targetid' => $targetid]);
      }
      DB::commit();
      $ret['message'] = '襲撃完了しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function save($me, $room, $targetid)
  {
    $ret = [];
    try {
      $actionName = 'save';
      if ($me->role != 2) {
        $ret['error'] = 'あなたは狩人ではありません。';
        return $ret;
      }
      if ($room->day > 1) {
        $lastTarget = DB::table('history')->where(
          [
            'roomid' => $room->id,
            'playerid' => $me->id,
            'day' => $room->day - 1,
            'time' => $room->time,
            'action' => $actionName,
          ]
        )->first();
        if ($lastTarget->targetid == $targetid) {
          $ret['error'] = '昨日と同じ人を守ることはできません。';
          return $ret;
        }
      }
      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);

      DB::beginTransaction();
      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        $myAction['targetid'] = $targetid;
        DB::table('history')->insert($myAction);
      } else {
        $action->update(['action' => $actionName, 'targetid' => $targetid]);
      }
      DB::commit();
      $ret['message'] = '防衛完了しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function predict($me, $room, $targetid)
  {
    $ret = [];
    try {
      $actionName = 'predict';
      if ($me->role != 3) {
        $ret['error'] = 'あなたは占い師ではありません。';
        return $ret;
      }
      $target = DB::table('player')->where(
        [
          'roomid' => $room->id,
          'id' => $targetid,
        ]
      )->first();
      if ($target->flgDead == 1) {
        $ret['error'] = '拉致・投獄されたプレイヤーを占うことはできません。';
        return $ret;
      }

      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);

      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        $myAction['targetid'] = $targetid;
        DB::beginTransaction();
        DB::table('history')->insert($myAction);
        DB::commit();
      } else {
        $ret['error'] = '1日に占うことができるのは1人のみです。';
      }
      $action = DB::table('history')->where($myAction)->first();
      $target = DB::table('player')->where(
        [
          'roomid' => $room->id,
          'id' => $action->targetid,
        ]
      )->first();
      if ($target->role == 5) {
        $target->role = 0;
      }
      $ret['result'][$actionName]['target'] = $target;
      if (!isset($ret['message'])) {
        $ret['message'] = $target->name . 'さんを占いました。';
      }
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function expose($me, $room, $targetid)
  {
    $ret = [];
    try {
      $actionName = 'expose';
      if ($me->role != 4) {
        $ret['error'] = 'あなたは霊媒師ではありません。';
        return $ret;
      }
      $target = DB::table('player')->where(
        [
          'roomid' => $room->id,
          'id' => $targetid,
        ]
      )->first();
      if ($target->flgDead == 0) {
        $ret['error'] = '霊媒対象は拉致・投獄されたプレイヤーだけです。';
        return $ret;
      }

      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);

      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        $myAction['targetid'] = $targetid;
        DB::beginTransaction();
        DB::table('history')->insert($myAction);
        DB::commit();
      } else {
        $ret['error'] = '1日に霊媒することができるのは1人のみです。';
      }
      $action = DB::table('history')->where($myAction)->first();
      $target = DB::table('player')->where(
        [
          'roomid' => $room->id,
          'id' => $action->targetid,
        ]
      )->first();

      $ret['result'][$actionName]['target'] = $target;
      if (!isset($ret['message'])) {
        $ret['message'] = $target->name . 'さんを霊媒しました。';
      }
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function change($me, $room, $targetid)
  {
    $ret = [];
    try {
      $actionName = 'change';
      if ($me->role != 7) {
        $ret['error'] = 'あなたは吸血鬼ではありません。';
        return $ret;
      }
      $target = DB::table('player')->where(
        [
          'roomid' => $room->id,
          'id' => $targetid,
        ]
      )->first();
      if ($target->flgDead == 1) {
        $ret['error'] = '襲撃対象は拉致・投獄されていないプレイヤーだけです。';
        return $ret;
      }

      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);

      DB::beginTransaction();
      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        $myAction['targetid'] = $targetid;
        DB::table('history')->insert($myAction);
      } else {
        $action->update(['action' => $actionName, 'targetid' => $targetid]);
      }
      DB::commit();
      $ret['message'] = '襲撃完了しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function freedom($me, $room, $targetid)
  {
    $ret = [];
    try {
      $actionName = 'freedom';
      if ($me->role != 9) {
        $ret['error'] = 'あなたは天使ではありません。';
        return $ret;
      }
      $target = DB::table('player')->where(
        [
          'roomid' => $room->id,
          'id' => $targetid,
        ]
      )->first();
      if ($target->flgDead == 0) {
        $ret['error'] = '救出対象は拉致・投獄されているプレイヤーだけです。';
        return $ret;
      }

      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);

      DB::beginTransaction();
      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        $myAction['targetid'] = $targetid;
        DB::table('history')->insert($myAction);
      } else {
        $action->update(['action' => $actionName, 'targetid' => $targetid]);
      }
      DB::commit();
      $ret['message'] = '救出完了しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function consider($me, $room, $targetid)
  {
    $ret = [];
    try {
      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'targetid' => 0,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);

      $rand = rand(1, 100);
      if ($rand > 50) {
        $actionName = 'sleep';
      } else {
        $actionName = 'consider';
      }
      $rand = rand(1, 100);
      if ($rand > 50) {
        $ret['result']['consider'] = 'voice';
      }

      DB::beginTransaction();
      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        DB::table('history')->insert($myAction);
      } else {
        $action->update(['action' => $actionName]);
      }
      DB::commit();
      $ret['message'] = '検討しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }
}
