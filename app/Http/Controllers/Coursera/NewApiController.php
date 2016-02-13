<?php

namespace App\Http\Controllers\Coursera;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class NewApiController extends Controller
{
    public function courses()
    {
        $client = new Client([
            'base_uri' => 'https://api.coursera.org/api/'
        ]);

        $response = $client->request('GET', 'courses.v1?start=101&limit=500');
        $contents = json_decode($response->getBody()->getContents(), true);

        return ($contents);
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
