<?php

namespace UNELearning\Coursera\NewApi;

use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    protected $table = 'coursera_new_api_instructors';

    protected $fillable = [
        'coursera_id',
        'photo',
        'photo150',
        'bio',
        'prefix_name',
        'first_name',
        'middle_name',
        'last_name',
        'suffix_name',
        'full_name',
        'title',
        'department',
        'website',
        'website_twitter',
        'website_facebook',
        'website_linkedin',
        'website_gplus',
        'short_name'
    ];
}
