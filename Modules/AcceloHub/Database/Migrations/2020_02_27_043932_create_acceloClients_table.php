<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcceloClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acceloClients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('accelo_client_id');
            $table->string('hubstaff_client_id');
            $table->longText('acceloClient_data')->nullable();
            $table->longText('hubstaffClient_data')->nullable();            
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
        Schema::dropIfExists('acceloClients');
    }
}
