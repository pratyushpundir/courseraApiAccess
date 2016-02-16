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
        'courses' => [
            'primary_languages','subtitle_languages','instructor_ids','partner_ids','certificates','specializations','s12n_ids','domain_types','categories'
        ],
        'partners' => ['course_ids','instructor_ids','links','location'],
        'instructors' => []
    ];

    public function courses()
    {
        $recordType = 'courses';
        $fields = [
            'primaryLanguages','subtitleLanguages','partnerLogo','instructorIds','partnerIds','photoUrl','certificates','description','startDate','workload','previewLink','specializations','s12nIds','domainTypes','categories'
        ];

        $rcvdCourseData = $this->getDataFromApi($recordType, null, null, $fields);
        $results = $this->saveRecords($rcvdCourseData, $recordType);

        return $results;
    }
    
    public function partners()
    {
        $recordType = 'partners';
        $fields = ['id','name','shortName','description','banner','courseIds','instructorIds','primaryColor','logo','squareLogo','rectangularLogo','links','location'
        ];

        $rcvdPartnerData = $this->getDataFromApi($recordType, null, null, $fields);
        $results = $this->saveRecords($rcvdPartnerData, $recordType);

        return $results;
    }

    public function instructors()
    {
        $recordType = 'instructors';
        $fields = [
            'id','photo','photo150','bio','prefixName','firstName','middleName','lastName','suffixName','fullName','title','department','website','websiteTwitter','websiteFacebook','websiteLinkedin','websiteGplus','shortName'
        ];

        $rcvdInstructorData = $this->getDataFromApi($recordType, null, null, $fields);
        $results = $this->saveRecords($rcvdInstructorData, $recordType);

        return $results;
    }

    public function coursesExport()
    {
        $this->export('courses');
    }
    
    public function partnersExport()
    {
        $this->export('partners');
    }
    
    public function instructorsExport()
    {
        $this->export('instructors');
    }

    public function getDataFromApi($item, $startAt, $recordsPerPage, array $fields)
    {
        $client = new Client([
            'base_uri' => 'https://api.coursera.org/api/'
        ]);

        $startAt = ($startAt == null) ? 1 : $startAt;
        $recordsPerPage = ($recordsPerPage == null) ? 100 : $recordsPerPage;

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

    public function saveRecords($records, $recordType)
    {
        $addedRecords = $skippedRecords = 0;
        
        foreach($records as $rcvdRecord) {
            $rcvdRecordId = $rcvdRecord['id'];
            $modelName = $this->getModelName($recordType);
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
            'record_name' => $recordType,
            'added' => $addedRecords,
            'skipped' => $skippedRecords
        ];
    }

    public function export($recordType)
    {
        $modelName = $this->getModelName($recordType);
        $savedRecords = $modelName::all()->toArray();

        $exportArray = [];
        $tempCourse = [];

        foreach($savedRecords as $savedRecord) {
            foreach($savedRecord as $key => $value) {
                if(in_array($key, $this->serializedAttributes[$recordType])) {
                    $tempCourse[$key] = json_encode(unserialize(base64_decode($value)));
                } else {
                    $tempCourse[$key] = $value;
                }
            }
            
            array_push($exportArray, $tempCourse);
        }

        Excel::create('un-coursera-data-new-api-' . $recordType, function($excel) use($recordType, $exportArray) {
            $excel->sheet($recordType, function($sheet) use($exportArray) {
                $sheet->fromArray($exportArray);
            });
        })->export('xlsx');
    }

    public function getModelName($recordType)
    {
        return 'UNELearning\Coursera\NewApi\\' . studly_case(str_singular($recordType));
    }
}
