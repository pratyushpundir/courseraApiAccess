@extends('layouts.basic')

@section('title', 'E-Learning Data')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h1 class="text-center">Coursera API Access</h1>

            <ul class="list-group" style="width:600px; margin: 0 auto;">
                <li class="list-group-item">
                    <a href="/coursera/new-api/courses">
                        Import Courses
                    </a> | 
                    <a href="/coursera/new-api/courses/export">
                        Export Courses
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="/coursera/new-api/partners">
                        Import Partners
                    </a> | 
                    <a href="/coursera/new-api/partners/export">
                        Export Partners
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="/coursera/new-api/Instructors">
                        Import Instructors
                    </a> | 
                    <a href="/coursera/new-api/instructors/export">
                        Export Instructors
                    </a>
                </li>
            </ul>
        </div>
    </div>
@endsection