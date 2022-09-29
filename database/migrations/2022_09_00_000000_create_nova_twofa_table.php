<?php

use Illuminate\Support\Facades\Schema;
use Outl1ne\NovaTwoFactor\NovaTwoFactor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('enabled')->default(false);
            $table->boolean('confirmed')->default(false);
            $table->string('secret')->nullable();
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
        $tableName = NovaTwoFactor::getTableName();
        Schema::dropIfExists($tableName);
    }
}
