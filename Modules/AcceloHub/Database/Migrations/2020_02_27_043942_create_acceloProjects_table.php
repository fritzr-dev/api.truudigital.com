<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcceloProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acceloProjects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('accelo_project_id');
            $table->string('hubstaff_project_id');
            $table->longText('acceloProj_data')->nullable();
            $table->longText('hubstaffProj_data')->nullable();
            $table->longText('accelo_Tasks')->nullable();
            $table->bigInteger('accelo_last_task')->nullable();
            $table->bigInteger('accelo_last_task_updated')->nullable();
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
        Schema::dropIfExists('acceloProjects');
    }
}
