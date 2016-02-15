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

    protected $serializedAttributes = [
        'primary_languages',
        'subtitle_languages',
        'instructor_ids',
        'partner_ids',
        'certificates',
        'specializations',
        's12n_ids',
        'domain_types',
        'categories'
    ];

    public function courses()
    {
        $rcvdCourseData = $this->getAllCourses();

        $addedRecords = $skippedRecords = 0;

        foreach($rcvdCourseData as $rcvdCourse) {
            $rcvdCourseId = $rcvdCourse['id'];

            $rcvdCourseExists = Course::where('coursera_id', $rcvdCourseId)->first();

            if(!$rcvdCourseExists) {
                $course = new Course;

                foreach($rcvdCourse as $key => $value) {
                    if($key == 'id') {
                        $dbPropertyName = 'coursera_id';
                    } else {
                        $dbPropertyName = snake_case($key);
                    }

                    if(is_array($value)) {
                        $course->{$dbPropertyName} = base64_encode(serialize($value));
                        // $course->{$dbPropertyName} = $value;
                    } else {
                        $course->{$dbPropertyName} = $value;
                    }
                }

                $course->save();

                $addedRecords++;
            } else {
                $skippedRecords++;
            }
        }

        return $results = [
            'added' => $addedRecords,
            'skipped' => $skippedRecords,
            'records' => Course::all()
        ];
    }
    
    public function coursesExport()
    {
        $savedCourseData = Course::all()->toArray();

        $exportArray = [];
        $tempCourse = [];

        foreach($savedCourseData as $savedCourse) {
            foreach($savedCourse as $key => $value) {
                if(in_array($key, $this->serializedAttributes)) {
                    $tempCourse[$key] = json_encode(unserialize(base64_decode($value)));
                } else {
                    $tempCourse[$key] = $value;
                }
            }
            
            array_push($exportArray, $tempCourse);
        }

        Excel::create('un-coursera-data-new-api', function($excel) use($exportArray) {
            $excel->sheet('Courses', function($sheet) use($exportArray) {
                $sheet->fromArray($exportArray);
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

    public function getAllCourses()
    {
        $client = new Client([
            'base_uri' => 'https://api.coursera.org/api/'
        ]);

        $startAt = 1;
        $recordsPerPage = 100;

        $response = $client->request('GET', 'courses.v1?start=' . $startAt . '&limit=' . $recordsPerPage);
        
        $contents = json_decode($response->getBody()->getContents(), true);

        $paging = $contents['paging'];
        $totalRecords = $paging['total'];
        
        $results = [];
        
        for($startAt = 1; $startAt <= $totalRecords + 1; $startAt += $recordsPerPage) {
            $response = $client->request('GET', 'courses.v1?start=' . $startAt . '&limit=' . $recordsPerPage . '&fields=primaryLanguages,subtitleLanguages,partnerLogo,instructorIds,partnerIds,photoUrl,certificates,description,startDate,workload,previewLink,specializations,s12nIds,domainTypes,categories');
        
            $contents = json_decode($response->getBody()->getContents(), true);
            
            foreach($contents['elements'] as $course) {
                array_push($results, $course);
            }
        }

        return $results;

    }
}
