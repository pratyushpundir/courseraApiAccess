<?php

namespace UNELearning\Http\Controllers\Coursera;

use UNELearning\Http\Controllers\Controller;
use Artisan;

class NewApiController extends Controller
{
    public function courses()
    {
        $commandResponse = Artisan::call('coursera:update', [
            '--recordType' => 'courses'
        ]);

        return ($commandResponse == 0) 
               ? 'Data successfully Updated and Persisted!' 
               : 'Oops! Something went wrong!';
    }

    
    public function partners()
    {
        $commandResponse = Artisan::call('coursera:update', [
            '--recordType' => 'partners'
        ]);

        return ($commandResponse == 0) 
               ? 'Data successfully Updated and Persisted!' 
               : 'Oops! Something went wrong!';
    }


    public function instructors()
    {
        $commandResponse = Artisan::call('coursera:update', [
            '--recordType' => 'instructors'
        ]);

        return ($commandResponse == 0) 
               ? 'Data successfully Updated and Persisted!' 
               : 'Oops! Something went wrong!';
    }

    public function all()
    {
        $commandResponse = Artisan::call('coursera:update', [
            '--recordType' => 'all'
        ]);

        return ($commandResponse == 0) 
               ? 'Data successfully Updated and Persisted!' 
               : 'Oops! Something went wrong!';
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

    
}
