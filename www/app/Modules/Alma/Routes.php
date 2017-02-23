<?php

Route::get('alma',                      'App\Modules\Alma\Controllers\AlmaController@index');

Route::post('alma/main-text',           'App\Modules\Alma\Controllers\AlmaController@postMainText');
Route::post('alma/list-words',          'App\Modules\Alma\Controllers\AlmaController@listWords');
Route::post('alma/add/{type}',          'App\Modules\Alma\Controllers\AlmaController@addTerm');
Route::post('alma/vote/{term_id}',      'App\Modules\Alma\Controllers\AlmaController@vote');
Route::post('alma/approve/{term_id}',   'App\Modules\Alma\Controllers\AlmaController@approve');
Route::post('alma/add-comment',         'App\Modules\Alma\Controllers\AlmaController@addComment');
