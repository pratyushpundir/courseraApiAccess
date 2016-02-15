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
        'primary_languages','subtitle_languages','instructor_ids','partner_ids','certificates','specializations','s12n_ids','domain_types','categories'
    ];

    public function courses()
    {
        $startAt = 1;
        $recordsPerPage = 100;
        $recordName = 'courses';
        $fields = [
            'primaryLanguages','subtitleLanguages','partnerLogo','instructorIds','partnerIds','photoUrl','certificates','description','startDate','workload','previewLink','specializations','s12nIds','domainTypes','categories'
        ];

        $rcvdCourseData = $this->getDataFromApi($recordName, $startAt, $recordsPerPage, $fields);

        $results = $this->saveRecords($rcvdCourseData, $recordName);

        return $results;
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
        $startAt = 1;
        $recordsPerPage = 100;
        $recordName = 'partners';
        $fields = ['id','name','shortName','description','banner','courseIds','instructorIds','primaryColor','logo','squareLogo','rectangularLogo','links','location'
        ];

        $rcvdPartnerData = $this->getDataFromApi($recordName, $startAt, $recordsPerPage, $fields);

        $results = $this->saveRecords($rcvdPartnerData, $recordName);

        return $results;
    }

    public function instructors()
    {
        $startAt = 1;
        $recordsPerPage = 100;
        $recordName = 'instructors';
        $fields = [
            'id','photo','photo150','bio','prefixName','firstName','middleName','lastName','suffixName','fullName','title','department','website','websiteTwitter','websiteFacebook','websiteLinkedin','websiteGplus','shortName'
        ];

        $rcvdCourseData = $this->getDataFromApi($recordName, $startAt, $recordsPerPage, $fields);

        $results = $this->saveRecords($rcvdCourseData, $recordName);

        return $results;
    }

    public function getDataFromApi($item, $startAt, $recordsPerPage, array $fields)
    {
        $client = new Client([
            'base_uri' => 'https://api.coursera.org/api/'
        ]);

        $fieldString = implode(',', $fields);


        $shortUrl = $item . '.v1?start=' . $startAt . '&limit=' . $recordsPerPage;
        
        $response = $client->request('GET', $shortUrl);
        $contents = json_decode($response->getBody()->getContents(), true);

        $paging = $contents['paging'];
        $totalRecords = (isset($paging['total'])) ? $paging['total'] : 1;
        
        $results = [];
        
        for($startAt = 1; $startAt <= $totalRecords + 1; $startAt += $recordsPerPage) {
            $response = $client->request('GET', $item . '.v1?start=' . $startAt . '&limit=' . $recordsPerPage . '&fields=' . $fieldString);
        
            $contents = json_decode($response->getBody()->getContents(), true);
            
            foreach($contents['elements'] as $course) {
                array_push($results, $course);
            }
        }

        return $results;

    }

    public function saveRecords($records, $recordName)
    {
        $addedRecords = $skippedRecords = 0;
        
        foreach($records as $rcvdRecord) {
            $rcvdRecordId = $rcvdRecord['id'];
            $modelName = 'UNELearning\Coursera\NewApi\\' . studly_case(str_singular($recordName));
            $rcvdRecordExists = $modelName::where('coursera_id', $rcvdRecordId)->first();
            if(!$rcvdRecordExists) {
                $record = new $modelName();
                foreach($rcvdRecord as $key => $value) {
                    if($key == 'id') {
                        $dbPropertyName = 'coursera_id';
                    } else {
                        $dbPropertyName = snake_case($key);
                    }
                    if(is_array($value)) {
                        $record->{$dbPropertyName} = base64_encode(serialize($value));
                        // $record->{$dbPropertyName} = $value;
                    } else {
                        $record->{$dbPropertyName} = $value;
                    }
                }
                $record->save();
                $addedRecords++;
            } else {
                $skippedRecords++;
            }
        }

        return $results = [
            'record_name' => $recordName,
            'added' => $addedRecords,
            'skipped' => $skippedRecords
        ];
    }
}
