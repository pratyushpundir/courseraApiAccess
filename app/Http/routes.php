<?php

Route::get('/', function () {
    return view('welcome');
});


Route::group(['prefix' => 'coursera/new-api'], function() {
    Route::get('courses', 'Coursera\NewApiController@courses');
    Route::get('courses/export', 'Coursera\NewApiController@coursesExport');

    Route::get('partners', 'Coursera\NewApiController@partners');
    Route::get('partners/export', 'Coursera\NewApiController@partnersExport');

    Route::get('instructors', 'Coursera\NewApiController@instructors');
    Route::get('instructors/export', 'Coursera\NewApiController@instructorsExport');
    
    Route::get('all', 'Coursera\NewApiController@all');
    Route::get('all/export', 'Coursera\NewApiController@allExport');
});