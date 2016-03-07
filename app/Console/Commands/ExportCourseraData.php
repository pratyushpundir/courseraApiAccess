<?php

namespace UNELearning\Console\Commands;

use Illuminate\Console\Command;
use Excel;
use Mail;

class ExportCourseraData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coursera:export {--recordType=all : Which record-type to export. Defaults to all.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports Coursera data for the given record-type';

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
            $fileInfo = $this->export($recordType);

            $data = ['record_types' => [$recordType]];

            Mail::send('emails.dataExport', $data, function($message) use($fileInfo) {
                $message->to(env('PRIMARY_ADMIN_EMAIL'))->cc(env('SECONDARY_ADMIN_EMAIL'))->subject('Latest Coursera data export!');
                $message->attach($fileInfo['full']);
            });

            $this->info('File exported and emailed to ' . env('PRIMARY_ADMIN_EMAIL') . ' & ' . env('SECONDARY_ADMIN_EMAIL'));
        } else {
            $recordTypes = ['courses', 'instructors', 'partners'];
            $fileInfo = [];

            $data = ['record_types' => []];

            foreach($recordTypes as $recordType) {
                $pathInfo = $this->export($recordType);
                array_push($fileInfo, $pathInfo);
                array_push($data['record_types'], $recordType);
            }

            Mail::send('emails.dataExport', $data, function($message) use($fileInfo) {
                $message->to(env('PRIMARY_ADMIN_EMAIL'))->cc(env('SECONDARY_ADMIN_EMAIL'))->subject('Latest Coursera data export!');
                foreach($fileInfo as $path) {
                    $message->attach($path['full']);
                }
            });

            $this->info('Files exported and emailed to ' . env('PRIMARY_ADMIN_EMAIL') . ' & ' . env('SECONDARY_ADMIN_EMAIL'));
        }
    }


    /**
     * Export stored data of a given record-type to Excel
     * @param  String $recordType [Record type to be exported]
     * @return Excel              [Downloads the created Excel export file]
     */
    protected function export($recordType)
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

        $pathInfo = Excel::create('coursera-new-api-' . $recordType, function($excel) use($recordType, $exportArray) {
            $excel->sheet($recordType, function($sheet) use($exportArray) {
                $sheet->fromArray($exportArray);
            });
        })->store('xlsx', storage_path('exports/coursera'), true);

        return $pathInfo;
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

}
