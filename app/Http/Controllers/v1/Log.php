<?php

namespace App\Http\Controllers\v1;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;
use Carbon\Carbon;

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
            $user = auth()->user();
            $no = $params['no'];
            $datas = $params['datas'];

            $log = [];
            DB::beginTransaction();
            try {
                $ret['cnt']['datas'] = 0;
                $ret['cnt']['errors'] = 0;
                foreach ($datas as $data) {
                    try{
                        $ret['cnt']['datas']++;
                        $data['user'] = $user->email;
                        $data['no'] = $no;
                        $data['create_at'] = date('Y-m-d H:i:s', $data['create_at']);

                        $where = [
                            'user' => $data['user'],
                            'no' => $data['no'],
                            'create_at' => $data['create_at'],
                        ];
                        $record = DB::table('hidata')->where($where);
                        if ($record->count() == 0) {
                            DB::table('hidata')->insert($data);
                            $log[] = $data;
                        } else {
                            $record->update($data);
                        }
                    } catch (\Exception $ex) {
                        $ret['cnt']['errors']++;
                    }
                }
                DB::commit();
                $ret['log'] = $log;
            } catch (\Exception $ex) {
                DB::rollback();
                $ret['code'] = 98;
                $ret['errors'][] = $ex;
                Logger::error($ex);
            }
        } catch (\Exception $ex) {
            $ret['code'] = 99;
            $ret['errors'][] = $ex;
            Logger::error($ex);
        }
        return response()->json($ret);
    }

    /**
     * ユーザが保有しているハイブリッドインバータの最新値をNoごとに取得する
     *
     * @param Request $request
     * @return void
     */
    public function getMyHybridInverters(Request $request)
    {
        try {
            $params = $request->query();
            $user = auth()->user();
            //noごとの最新を取ってくる
            $sql = "SELECT * FROM hidata AS h1 WHERE user='" . $user->email . "' AND create_at = (SELECT MAX(create_at) FROM hidata AS h2 WHERE h1.no = h2.no)";
            $data = DB::select($sql);
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
            $user = auth()->user();
            //15分単位時刻
            $interval = 15;

            //データ
            $sql = "SELECT ";
            $sql .= "battery_voltage, ";
            $sql .= "battery_current, ";
            $sql .= "battery_charge_power / 1000 as battery_charge_power, ";
            $sql .= "battery_soc, ";
            $sql .= "battery_max_charge_current, ";
            $sql .= "pv_voltage, ";
            $sql .= "pv_current, ";
            $sql .= "pv_power / 1000 as pv_power, ";
            $sql .= "pv_battery_charge_current, ";
            $sql .= "grid_voltage, ";
            $sql .= "grid_input_current, ";
            $sql .= "grid_battery_charge_current, ";
            $sql .= "grid_frequency, ";
            $sql .= "grid_battery_charge_max_current, ";
            $sql .= "inverter_voltage, ";
            $sql .= "inverter_current, ";
            $sql .= "inverter_frequency, ";
            $sql .= "inverter_power / 1000 as inverter_power, ";
            $sql .= "inverter_output_priority, ";
            $sql .= "inverter_charger_priority, ";
            $sql .= "temp_dc, temp_ac, temp_tr, create_at,";
            $sql .= "DATE_FORMAT(FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(create_at) / 900) * 900) ,'%H:%i') AS timestamp";
            $sql .= " FROM hidata ";
            $sql .= " WHERE user='" . $user->email . "' ";
            $sql .= " AND no ='" . $params['no'] . "' ";
            $sql .= " AND create_at BETWEEN '" . $params['date'] . " 00:00:00' AND '" . $params['date'] . " 23:59:59' ";
            $sql .= " ORDER BY create_at ASC";
            $rec = DB::select($sql);

            $datas = [];
            if (!empty($rec)) {
                $start = Carbon::createFromTime(0, 0);
                $end = Carbon::createFromTime(23, 45);
                $interval = 15; // 15分間隔

                $columns = array_keys((array)$rec[0]);
                while ($start->lessThanOrEqualTo($end)) {
                    $timestamp = $start->format('H:i');
                    $row = [];
                    $buf = [];
                    foreach ($columns as $column) {
                        $row[$column] = 0;
                    }
                    foreach ($rec as $data) {
                        if ($data->timestamp == $timestamp) {
                            foreach ($columns as $column) {
                                $buf[$column][] = $data->$column;
                            }
                        }
                    }
                    if (count($buf) > 0) {
                        foreach ($buf as $column => $lst) {
                            if ($column != "create_at" && $column != "timestamp") {
                                if ($column == "inverter_output_priority" || $column == "inverter_charger_priority") {
                                    $row[$column] = max($lst);
                                } else {
                                    $row[$column] = array_sum($lst) / count($lst);
                                }
                            }
                        }
                    }
                    //値が0なら配列に入れない
                    if ($row['battery_voltage'] > 0) {
                        $datas[$timestamp] = $row;
                    }
                    //次の時刻へ
                    $start->addMinutes($interval);
                }
            }

            $ret['code'] = 0;
            $ret['data']['interval'] = $interval;
            $ret['data']['datas'] = $datas;
            //$ret['authorisation'] = ['token' => Auth::refresh(), 'type' => 'bearer'];
        } catch (\Exception $ex) {
            $ret['code'] = 99;
            $ret['errors'][] = $ex;
        }
        return response()->json($ret);
    }
}
