<?php

namespace UNELearning\Http\Controllers\Coursera;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use UNELearning\Http\Requests;
use UNELearning\Http\Controllers\Controller;
use UNELearning\Coursera\NewApi\Course;
use Schema;
use Excel;
use Log;

class NewApiController extends Controller
{

    /**
     * Attributes for each record type that will be serialized
     * @var [type]
     */
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
        $fields = $this->getFieldNames($recordType);
        
        $rcvdCourseData = $this->getDataFromApi($recordType, $fields);
        $results = $this->persistRecords($rcvdCourseData, $recordType);

        return $results;
    }

    
    public function partners()
    {
        $recordType = 'partners';
        $fields = $this->getFieldNames($recordType);

        $rcvdPartnerData = $this->getDataFromApi($recordType, $fields);
        $results = $this->persistRecords($rcvdPartnerData, $recordType);

        return $results;
    }


    public function instructors()
    {
        $recordType = 'instructors';
        $fields = $this->getFieldNames($recordType);

        $rcvdInstructorData = $this->getDataFromApi($recordType, $fields);
        $results = $this->persistRecords($rcvdInstructorData, $recordType);

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


    /**
     * Access the Coursera API and get requested record type data
     * @param  String  $recordType     [Which record type to pull from the API]
     * @param  Array   $fields         [All fields that are to be pulled for the given record type]
     * @param  Integer $startAt        [Starting record number]
     * @param  Integer $recordsPerPage [No. of records per page]
     * @return Array   $results        [Records received from all pages of the API data]
     */
    public function getDataFromApi($recordType, Array $fields, $startAt = null, $recordsPerPage = null)
    {
        // Setup the HTTP client
        $client = new Client([
            'base_uri' => 'https://api.coursera.org/api/'
        ]);

        // Some defaults for starting page number and number of records per page
        $startAt = ($startAt == null) ? 1 : $startAt;
        $recordsPerPage = ($recordsPerPage == null) ? 100 : $recordsPerPage;

        // Prepare a short request with not much data pulled just to access the paging info        
        $shortUrl = $recordType . '.v1?start=' . $startAt . '&limit=' . $recordsPerPage;
        $response = $client->request('GET', $shortUrl);
        $contents = json_decode($response->getBody()->getContents(), true);

        // Get number of pages and total records
        $paging = $contents['paging'];
        $totalRecords = (isset($paging['total'])) ? $paging['total'] : 1;
        
        $results = [];
        
        // Derive a string of comma-separated string of all fields that are to be pulled
        $fieldString = implode(',', $fields);
        
        // Get data from each page of the paginated results from the API
        for($startAt; $startAt <= $totalRecords + 1; $startAt += $recordsPerPage) {
            $response = $client->request('GET', $recordType . '.v1?start=' . $startAt . '&limit=' . $recordsPerPage . '&fields=' . $fieldString);
        
            $contents = json_decode($response->getBody()->getContents(), true);
            
            foreach($contents['elements'] as $record) {
                array_push($results, $record);
            }
        }

        return $results;

    }


    /**
     * Persist provided records to Database table of the given record type
     * @param  Array  $records     [Array of all records to be persisted]
     * @param  String $recordType  [Type of record]
     * @return Array $results      [Stats related to this operation]
     */
    public function persistRecords($records, $recordType)
    {
        $addedRecords = $skippedRecords = 0;
        
        foreach($records as $rcvdRecord) {
            $rcvdRecordId = $rcvdRecord['id'];
            
            // Derive the proper model classname and instantiate it as needed
            $modelName = $this->getModelName($recordType);

            // Persist record if it doesn't already exist in database
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
            'record_type' => $recordType,
            'added' => $addedRecords,
            'skipped' => $skippedRecords
        ];
    }

    /**
     * Export stored data of a given record-type to Excel
     * @param  String $recordType [Record type to be exported]
     * @return Excel              [Downloads the created Excel export file]
     */
    public function export($recordType)
    {
        $modelName = $this->getModelName($recordType);
        $savedRecords = $modelName::all()->toArray();

        $exportArray = [];
        $tempCourse = [];

        foreach($savedRecords as $savedRecord) {
            foreach($savedRecord as $key => $value) {

                // Check to see if the given attribute is a serialized type
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

    /**
     * Derive the model class name for a given record type
     * @param  String $recordType [Type of record]
     * @return String             [Fully qualified classname for the model]
     */
    public function getModelName($recordType)
    {
        return 'UNELearning\Coursera\NewApi\\' . studly_case(str_singular($recordType));
    }

    /**
     * Get Field names from the relevant table attribute names.
     * We use snake_case for attribute names while Coursera 
     * uses camelCase.
     * @param  [String] $recordType [Type of the record - 'courses', 'partners' or 'instructors']
     * @return [Array]              [An array of field names]
     */
    public function getFieldNames($recordType) 
    {
        $attributes = Schema::getColumnListing('coursera_new_api_' . $recordType);
        $fields = [];

        foreach($attributes as $attribute) {
            // Since 'coursera_id' in our DB is simply 'id' in data rcvd from the API
            if($attribute !== 'coursera_id') {
                array_push($fields, camel_case($attribute));
            }
        }

        return $fields;
    }
}
