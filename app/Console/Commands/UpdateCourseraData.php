<?php

namespace UNELearning\Console\Commands;

use UNELearning\Coursera\NewApi\Course;
use Illuminate\Console\Command;
use Schema, Artisan, Log;
use GuzzleHttp\Client;
use Illuminate\Http\Response;

class UpdateCourseraData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coursera:update {--recordType=all : Which record-type to update. Defaults to all.}';

    /**
     * Attributes for each Coursera record type that will be serialized
     * @var [type]
     */
    protected $serializedAttributes = [
        'courses' => [
            'primary_languages','subtitle_languages','instructor_ids','partner_ids','certificates','specializations','s12n_ids','domain_types','categories'
        ],
        'partners' => ['course_ids','instructor_ids','links','location'],
        'instructors' => []
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Coursera data for the given record-type';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $recordType = $this->option('recordType');

        if($recordType !== 'all') {
            $results = $this->updateAndPersist($recordType);

            $this->info('Record Type : ' . $results['record_type']);
            $this->info('Added : ' . $results['added']);
            $this->info('Skipped : ' . $results['skipped']);
        } else {
            $results = [];
            $recordTypes = ['courses', 'partners', 'instructors'];
            
            foreach($recordTypes as $recordType) {
                $result = $this->updateAndPersist($recordType);
                array_push($results, $result);
            }

            $headers = ['Record Type', 'Added', 'Skipped'];
            $this->table($headers, $results);
        }

        return response()->json($results);
    }


    /**
     * Queries the API and updates the database with updated records
     * @param  String $recordType [Record Type]
     * @return Array              [Number of added and skipped records]
     */
    protected function updateAndPersist($recordType)
    {
        $fields = $this->getFieldNames($recordType);
        
        $rcvdData = $this->getDataFromApi($recordType, $fields);
        $results = $this->persistRecords($rcvdData, $recordType);

        return $results;
    }


    /**
     * Access the Coursera API and get requested record type data
     * @param  String  $recordType     [Which record type to pull from the API]
     * @param  Array   $fields         [All fields that are to be pulled for the given record type]
     * @param  Integer $startAt        [Starting record number]
     * @param  Integer $recordsPerPage [No. of records per page]
     * @return Array   $results        [Records received from all pages of the API data]
     */
    protected function getDataFromApi($recordType, Array $fields, $startAt = null, $recordsPerPage = null)
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
    protected function persistRecords($records, $recordType)
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

        $results = [
            'record_type' => $recordType,
            'added' => $addedRecords,
            'skipped' => $skippedRecords
        ];

        return $results;

    }


    /**
     * Derive the model class name for a given record type
     * @param  String $recordType [Type of record]
     * @return String             [Fully qualified classname for the model]
     */
    protected function getModelName($recordType)
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
    protected function getFieldNames($recordType) 
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
