<?php

Route::get('/', function () {
    return view('welcome');
});


Route::group(['prefix' => 'coursera/new-api'], function() {
    Route::get('courses', 'Coursera\NewApiController@courses');
    Route::get('partners', 'Coursera\NewApiController@partners');
    Route::get('instructors', 'Coursera\NewApiController@instructors');
});