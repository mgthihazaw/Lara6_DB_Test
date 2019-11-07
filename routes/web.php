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

use App\Account;
use Illuminate\Support\Facades\DB;
use App\User;

use function PHPSTORM_META\type;

DB::enableQueryLog(); // Enable query log


Route::get('/retrieve', function () {
    $users = DB::table('users')->get();


    // dd(DB::getQueryLog());
    dd($users);
});

//Retrieve Single Row
Route::get('/single-retrieve', function () {
    // $user = DB::table('users')->where('name', 'Orie Mraz')->first();  //get all column
    // $user = DB::table('users')->where('name', 'Orie Mraz')->value('email'); //get only email column
    $user = DB::table('users')->find(3);
    dd($user);
});

//Retrieve list of Column Values
Route::get('/list-retrieve', function () {
    // $users = DB::table('users')->pluck('name');  //get only  name value

    $users = DB::table('users')->pluck('id', 'name'); //get [1  =>  thihazaw,2=> mgmg]

    dd($users);
});

//Chunking Results
Route::get('/chunk', function () {

    // $users = DB::table('users')->orderBy('id')->chunk(100, function ($users) {
    //     foreach ($users as $user) {
    //         $user = User::findOrFail($user->id);

    //         $user->update(['active' => 0]);
    //     }
    // });

    //ChunkByID
    $users = DB::table('users')->where('active', false)
        ->chunkById(100, function ($users) {
            foreach ($users as $user) {
                if ($user->id % 2 == 0) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['active' => 1]);
                }
            }
        });

    dd($users);
});

//Aggregates
Route::get('/aggregate', function () {
    // $users = DB::table('users')->count();  
    // $users = DB::table('users')->max('name');
    // $users = DB::table('users')->avg('id');
    $user = DB::table('users')->where('active', 0)->exists();
    $user = DB::table('users')->where('active', 1)->doesntExist();
    dd($user);
});

//Select
Route::get('/select', function () {
    // $users = DB::table('users')->select('name', 'email as user_email')->get();
    // $users = DB::table('users')->distinct()->get();
    $query = DB::table('users')->select('name');
    $users = $query->addSelect('active')->get();
    return $users;
});

//Raw Expressons
Route::get('/raw', function () {

    $users = DB::table('users')
        ->select(DB::raw('count(*) as count,name'))

        ->groupBy('name')
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});
//Select Raw Method
Route::get('/select-raw', function () {

    $users = DB::table('users')
        ->selectRaw('active * ? as activeUser,email,name', [0])
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});

//WhereRaw /orWhereRaw Method
Route::get('/where-raw', function () {

    $users = DB::table('users')->whereRaw('active = ?', [1])
        ->orWhereRaw('active = ?', [0])->get();
    // dd(DB::getQueryLog());
    return $users;
});

// havingRaw / orHavingRaw

Route::get('/having-raw', function () {

    $users = DB::table('users')
        ->selectRaw('active,COUNT(active) as total')
        ->groupBy('active')
        ->having('total', '>', 174)
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});

// OrderByRaw

Route::get('/order-raw', function () {

    $users = DB::table('users')
        ->orderByRaw(' created_at DESC')
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});

/***********JOIN Query**************/
Route::get('/join', function () {

    $users = DB::table('users')
        ->join('accounts', 'users.id', '=', 'accounts.user_id')
        ->where('users.id', '=', 1)
        ->select('users.name', 'users.email', 'accounts.phone', 'accounts.address')
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});

//LeftJoin
Route::get('/left-join', function () {

    $users = DB::table('users')
        ->leftjoin('accounts', 'users.id', '=', 'accounts.user_id')
        ->select('users.*', 'accounts.*')
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});
// Rigth join
Route::get('/right-join', function () {
    // Account::create([
    //     'username' => 'Zawzaw',
    //     'address'  => 'SGG',
    //     'city'     => 'SGG',
    //     'state'    => 'SGG',
    //     'phone'    => '09 - 496997696',
    //     'user_id'  => 21,

    // ]);
    $users = DB::table('users')
        ->rightjoin('accounts', 'users.id', '=', 'accounts.user_id')
        ->select('users.*', 'accounts.*')
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});

//CrossJoin  
Route::get('/cross-join', function () {
    // [
    // user1 ->account1,
    // user1 ->account2
    //user1->account3
    // user2 ->account1,
    // user2 ->account2
    //user2->account3
    // ]
    $users = DB::table('users')
        ->crossJoin('accounts')
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});

// Advanced Join Clauses
Route::get('/advanced-join', function () {

    $users = DB::table('users')
        ->join('accounts', function ($join) {
            $join->on('users.id', '=', 'accounts.user_id')
                ->where('accounts.user_id', '>', 11);
        })
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});

// Sub-Query Joins
Route::get('/subquery', function () {

    $posts = DB::table('posts')
        ->select('user_id', DB::raw('MAX(created_at) as last_time'))
        ->groupBy('user_id');

    $users = DB::table('users')
        ->joinSub($posts, 'posts', function ($join) {
            $join->on('users.id', '=', 'posts.user_id');
        })->get();
    dd(DB::getQueryLog());
    return $users;
});

//Unions
Route::get('/unions', function () {

    $first = DB::table('users')
        ->where('active', 0);

    $users = DB::table('users')
        ->where('active', 1)
        ->union($first)
        ->get();
    return $users;
});