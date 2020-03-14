<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcceloTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('AcceloTickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('accelo_ticket_id');
            $table->string('hubstaff_task_id');
            $table->longText('acceloTicket_data')->nullable();
            $table->longText('hubstaffTask_data')->nullable();
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
        Schema::dropIfExists('AcceloTickets');
    }
}
