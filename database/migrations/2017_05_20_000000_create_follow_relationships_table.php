<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFollowRelationshipsTable extends Migration
{
    /**
     * Get a follow relationships table name.
     *
     * @return string
     */
    private function relationshipsTableName()
    {
        return config('follow.table_name');
    }

    /**
     * Get a user model key name.
     *
     * @return string
     */
    private function userKeyName()
    {
        $userModel = config('follow.user');

        return (new $userModel)->getKeyName();
    }

    /**
     * Get a users table name.
     *
     * @return string
     */
    private function usersTableName()
    {
        $userModel = config('follow.user');

        return (new $userModel)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->relationshipsTableName(), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('follower_id');
            $table->unsignedInteger('followee_id');
            $table->timestamp('followed_at');

            $table->unique(['follower_id', 'followee_id']);

            $key = $this->userKeyName();
            $tableName = $this->usersTableName();

            $table->foreign('follower_id')
                ->references($key)
                ->on($tableName)
                ->onDelete('cascade');

            $table->foreign('followee_id')
                ->references($key)
                ->on($tableName)
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->relationshipsTableName());
    }
}
