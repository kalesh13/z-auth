<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZrolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zroles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index();
            $table->string('role', 100)->default('Customer');
            $table->integer('score')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zroles');
    }
}
