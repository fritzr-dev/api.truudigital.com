<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcceloTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acceloTasks', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('project_id')->unsigned();
            $table->string('accelo_task_id');
            $table->string('hubstaff_task_id');
            $table->longText('acceloTask_data')->nullable();
            $table->longText('hubstaffTask_data')->nullable();             
            $table->string('type')->nullable();             
            $table->timestamps();
            $table->integer('status')->default('0');
            $table->foreign('project_id')->references('id')->on('acceloProjects')
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
        Schema::dropIfExists('acceloTasks');
    }
}
