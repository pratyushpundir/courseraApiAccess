<?php

namespace UNELearning\Http\Controllers\Coursera;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use UNELearning\Http\Requests;
use UNELearning\Http\Controllers\Controller;
use UNELearning\Coursera\NewApi\Course;
use Excel;
use Log;

class NewApiController extends Controller
{
    public function courses()
    {
        $client = new Client([
            'base_uri' => 'https://api.coursera.org/api/'
        ]);

        $response = $client->request('GET', 'courses.v1?start=1&limit=2000&fields=primaryLanguages,subtitleLanguages,partnerLogo,instructorIds,partnerIds,photoUrl,certificates,description,startDate,workload,previewLink,specializations,s12nIds,domainTypes,categories');
        $contents = json_decode($response->getBody()->getContents(), true);
        $rcvdCourseData = $contents['elements'];

        foreach($rcvdCourseData as $rcvdCourse) {
            $course = new Course;

            foreach($rcvdCourse as $key => $value) {
                if($key == 'id') {
                    $dbPropertyName = 'coursera_id';
                } else {
                    $dbPropertyName = snake_case($key);
                    // Log::warning(Log::warning($key . ' => ' . $dbPropertyName . ' => ' . serialize($value)));
                }

                $course->{$dbPropertyName} = is_array($value) ? serialize($value) : $value;
            }

            $course->save();
        }

        return Course::all();
    }
    
    public function coursesExport()
    {
        $savedCourseData = Course::all();

        Excel::create('un-coursera-data-new-api', function($excel) use($savedCourseData) {
            $excel->sheet('Courses', function($sheet) use($savedCourseData) {
                $sheet->fromModel($savedCourseData);
            });
        })->export('xlsx');
    }
    
    public function partners()
    {
        $client = new Client([
            'base_uri' => 'https://api.coursera.org/api/'
        ]);

        $response = $client->request('GET', 'partners.v1?start=101&limit=500');
        $contents = json_decode($response->getBody()->getContents(), true);

        return ($contents);
    }

    public function instructors()
    {
        $client = new Client([
            'base_uri' => 'https://api.coursera.org/api/'
        ]);

        $response = $client->request('GET', 'instructors.v1?start=101&limit=500');
        $contents = json_decode($response->getBody()->getContents(), true);

        return ($contents);
    }
}
