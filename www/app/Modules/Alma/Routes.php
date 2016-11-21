<?php

Route::get('alma',                      'App\Modules\Alma\Controllers\AlmaController@index');

Route::post('alma/list-words',          'App\Modules\Alma\Controllers\AlmaController@listWords');
Route::post('alma/add/{type}',          'App\Modules\Alma\Controllers\AlmaController@addTerm');