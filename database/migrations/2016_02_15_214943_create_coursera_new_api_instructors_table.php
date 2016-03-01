<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCourseraNewApiInstructorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coursera_new_api_instructors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('coursera_id')->unique();
            $table->string('photo');
            $table->string('photo150');
            $table->longText('bio');
            $table->string('prefix_name');
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('last_name');
            $table->string('suffix_name');
            $table->string('full_name');
            $table->string('title');
            $table->string('department');
            $table->string('website');
            $table->string('website_twitter');
            $table->string('website_facebook');
            $table->string('website_linkedin');
            $table->string('website_gplus');
            $table->string('short_name');
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
        Schema::drop('coursera_new_api_instructors');
    }
}
