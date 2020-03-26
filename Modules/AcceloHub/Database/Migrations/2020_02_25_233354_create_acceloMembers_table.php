<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcceloMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acceloMembers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('accelo_member_id')->unique();
            $table->string('hubstaff_member_id')->unique();
            $table->longText('hubstaff_full_name')->nullable();
            $table->longText('accelo_data')->nullable();
            $table->longText('hubstaff_data')->nullable();
            $table->integer('status')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acceloMembers');
    }
}
