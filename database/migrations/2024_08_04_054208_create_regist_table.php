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
            $table->integer('mode')->length(1)->comment('0:都度、1:恒久');
            $table->string('regist', 255);
            $table->string('result', 100);
            $table->datetime('create_at');
            $table->datetime('done_at')->nullable();
            $table->primary(['user', 'no', 'mode']);
        });
        Schema::create('hi', function (Blueprint $table) {
            $table->string('user', 100);
            $table->string('no', 100);
            $table->string('ip', 40);
            $table->string('looop_area', 2);
            $table->float('lat')->nullable();
            $table->float('lon')->nullable();
            $table->datetime('create_at');
            $table->primary(['user', 'no']);
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
        Schema::dropIfExists('hi');
    }
};
