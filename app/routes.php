<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

// Main app
Route::get('/', 'HomeController@getIndex');
Route::controller('install', 'InstallController');
Route::get('/manage', 'ManageController@getIndex');

// Projects
Route::get('projects/position', 'ProjectsController@position');
Route::resource('projects', 'ProjectsController');

// Specification
Route::controller('/specification', 'SpecificationController');