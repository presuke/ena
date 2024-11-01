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
            //noごとの最新を取ってくる
            $sql = "SELECT * FROM hidata AS h1 WHERE user='" . $token->name . "' AND create_at = (SELECT MAX(create_at) FROM hidata AS h2 WHERE h1.no = h2.no)";
            $data = DB::select($sql);
            $ret['code'] = 0;
            $ret['data'] = $data;
            //標準出力&ログに出力するメッセージのフォーマット
            $message = '[' . date('Y-m-d h:i:s') . ']' . json_encode($ret);

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
