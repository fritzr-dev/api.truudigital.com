<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcceloActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acceloActivity', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->string('accelo_activity_id');
            $table->string('hubstaff_activity_id');
            $table->longText('acceloActivity_data')->nullable();
            $table->longText('hubstaffActivity_data')->nullable();  
            $table->longText('acceloPost_data')->nullable();  
            $table->longText('api_error')->nullable();  
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
        Schema::dropIfExists('acceloActivity');
    }
}
