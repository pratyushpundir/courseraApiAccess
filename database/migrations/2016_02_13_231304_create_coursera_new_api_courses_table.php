<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCourseraNewApiCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coursera_new_api_courses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('coursera_id')->unique();
            $table->string('slug');
            $table->string('name');
            $table->string('course_type');
            $table->longText('primary_languages');
            $table->longText('subtitle_languages');
            $table->string('partner_logo');
            $table->longText('instructor_ids');
            $table->longText('partner_ids');
            $table->string('photo_url');
            $table->longText('certificates');
            $table->longText('description');
            $table->dateTime('start_date');
            $table->longText('workload');
            $table->string('preview_link');
            $table->longText('specializations');
            $table->longText('s12n_ids');
            $table->longText('domain_types');
            $table->longText('categories');
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
        Schema::drop('coursera_new_api_courses');
    }
}
