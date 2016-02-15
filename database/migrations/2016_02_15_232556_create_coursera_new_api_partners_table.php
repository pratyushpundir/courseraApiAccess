<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCourseraNewApiPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coursera_new_api_partners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('coursera_id');
            $table->string('name');
            $table->string('short_name');
            $table->string('description');
            $table->string('banner');
            $table->string('course_ids');
            $table->string('instructor_ids');
            $table->string('primary_color');
            $table->string('logo');
            $table->string('square_logo');
            $table->string('rectangular_logo');
            $table->string('links');
            $table->string('location');
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
        Schema::drop('coursera_new_api_partners');
    }
}
