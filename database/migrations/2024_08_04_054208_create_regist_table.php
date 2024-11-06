<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regist', function (Blueprint $table) {
            $table->string('user', 100);
            $table->string('no', 100);
            $table->string('regist', 100);
            $table->string('value', 100);
            $table->string('result', 100);
            $table->datetime('create_at');
            $table->datetime('done_at')->nullable();
            $table->primary(['user', 'no', 'regist', 'done_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regist');
    }
};
