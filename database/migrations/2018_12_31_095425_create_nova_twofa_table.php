<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Outl1ne\NovaTwoFactor\NovaTwoFactor;

class CreateNovaTwoFaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = NovaTwoFactor::getTableName();
        $userTable = (new (config('nova-two-factor.user_model')))->getTable();
        $userKey = (new (config('nova-two-factor.user_model')))->getKeyName();

        Schema::create($tableName, function (Blueprint $table) use ($userTable, $userKey) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('google2fa_enabled')->default(false);
            $table->boolean('confirmed')->default(false);
            $table->string('google2fa_secret')->nullable();
            $table->text('recovery')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references($userKey)
                ->on($userTable);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nova_twofa');
    }
}
