<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('team_id');
            $table->string('unsubscribed_status', 191);
            $table->string('first_name', 191)->nullable();
            $table->string('last_name', 191)->nullable();
            $table->string('phone', 191);
            $table->string('email', 191)->nullable();
            $table->integer('sticky_phone_number_id')->nullable();
            $table->string('twitter_id', 191)->nullable();
            $table->string('fb_messenger_id', 191)->nullable();
            $table->nullableTimestamps();
            $table->string('time_zone', 191)->nullable();
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->index('first_name');
            $table->index('phone');
            $table->index('twitter_id');
            $table->index(['team_id', 'phone'], 'idx_phone_search');
            $table->index('team_id');
            $table->index('last_name');
            $table->index('email');
            $table->index('fb_messenger_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('contacts');
    }

}