<?php

/*
|--------------------------------------------------------------------------
| Query Bilder
|--------------------------------------------------------------------------
|
| Laravel's database query builder provides a convenient, fluent interface
| to creating and running database queries
|
*/

use Illuminate\Support\Facades\DB;

Route::get('/retrieve', function () {
    $users = DB::table('users')->get();
    dd($users);
});