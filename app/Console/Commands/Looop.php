<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use DB;
use Carbon\Carbon;

class Looop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'looop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $url = "https://looop-denki.com/api/prices?select_area=01";

            //cURLセッションを初期化する
            $ch = curl_init();

            //URLとオプションを指定する
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //URLの情報を取得する
            $res =  curl_exec($ch);

            //標準出力&ログに出力するメッセージのフォーマット
            $message = '[' . date('Y-m-d h:i:s') . ']' . $res;

            //INFOレベルでメッセージを出力
            $this->info($message);
            //ログを書き出す処理はこちら
            Log::setDefaultDriver('batch');
            Log::info($message);
            return Command::SUCCESS;
        } catch (\Exception $ex) {
            $ret['code'] = 99;
            $ret['errors'][] = $ex;
            return Command::FAILURE;
        }
    }
}
