<?php

namespace UNELearning\Coursera\NewApi;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'coursera_new_api_courses';

    protected $dates = [
        'start_date'
    ];

    protected $fillable = [
        'coursera_id',
        'slug',
        'primary_languages',
        'subtitle_languages',
        'partner_logo',
        'instructor_ids',
        'partner_ids',
        'photo_url',
        'certificates',
        'description',
        'start_date',
        'workload',
        'preview_link',
        'specializations',
        's12n_ids',
        'domain_type',
        'categories'
    ];

    
    // protected $casts = [
    //     'primary_languages' => 'array',
    //     'subtitle_languages' => 'array',
    //     'instructor_ids' => 'array',
    //     'partner_ids' => 'array',
    //     'certificates' => 'array',
    //     'specializations' => 'array',
    //     's12n_ids' => 'array',
    //     'domain_types' => 'array',
    //     'categories' => 'array'
    // ];
}
