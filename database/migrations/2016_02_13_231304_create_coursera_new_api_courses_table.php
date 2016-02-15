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
            $table->text('primary_languages');
            $table->text('subtitle_languages');
            $table->string('partner_logo');
            $table->text('instructor_ids');
            $table->text('partner_ids');
            $table->string('photo_url');
            $table->text('certificates');
            $table->text('description');
            $table->dateTime('start_date');
            $table->text('workload');
            $table->string('preview_link');
            $table->text('specializations');
            $table->text('s12n_ids');
            $table->text('domain_types');
            $table->text('categories');
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
