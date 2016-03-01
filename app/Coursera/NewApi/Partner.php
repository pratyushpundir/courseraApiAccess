<?php

namespace UNELearning\Coursera\NewApi;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $table = 'coursera_new_api_partners';

    protected $fillable = [
        'coursera_id',
        'name',
        'short_name',
        'description',
        'banner',
        'course_ids',
        'instructor_ids',
        'primary_color',
        'logo',
        'square_logo',
        'rectangular_logo',
        'links',
        'location'
    ];
}
