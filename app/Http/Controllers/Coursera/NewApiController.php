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
        $commandResponse = Artisan::call('coursera:export', [
            '--recordType' => 'courses'
        ]);

        return ($commandResponse == 0) 
               ? 'Data successfully exported and emailed!' 
               : 'Oops! Something went wrong!';
    }
    

    public function partnersExport()
    {
        $commandResponse = Artisan::call('coursera:export', [
            '--recordType' => 'partners'
        ]);

        return ($commandResponse == 0) 
               ? 'Data successfully exported and emailed!' 
               : 'Oops! Something went wrong!';
    }
    

    public function instructorsExport()
    {
        $commandResponse = Artisan::call('coursera:export', [
            '--recordType' => 'instructors'
        ]);

        return ($commandResponse == 0) 
               ? 'Data successfully exported and emailed!' 
               : 'Oops! Something went wrong!';
    }

    
    public function allExport()
    {
        $commandResponse = Artisan::call('coursera:export', [
            '--recordType' => 'all'
        ]);

        return ($commandResponse == 0) 
               ? 'Data successfully exported and emailed!' 
               : 'Oops! Something went wrong!';
    }
    
}
