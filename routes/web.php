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
use Illuminate\Support\Str;


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
    // dd(DB::getQueryLog());
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

//OrWhere Clauses
Route::get('/orwhere', function () {
    $users = DB::table('users')
        ->where('id', '>', 10)
        ->orWhere('active', '1')
        ->get();
    return $users;
});

//WhereBetween
Route::get('/wherebetween', function () {
    $users = DB::table('users')
        ->whereBetween('id', [10, 15])
        ->get();
    return $users;
});

//WhereBetween
Route::get('/wherenotbetween', function () {
    $users = DB::table('users')
        ->whereNotBetween('id', [10, 15])
        ->get();
    return $users;
});

// whereIn// orWhereIn 
Route::get('/wherein', function () {
    $users = DB::table('accounts')
        ->whereIn('user_id', [1, 2, 3])
        ->orWhereIn('user_id', [6, 7, 8])
        ->get();

    return $users;
});

// whereNotIn // orWhereNotIn
Route::get('/wherenotin', function () {
    $users = DB::table('accounts')
        ->whereNotIn('user_id', [1, 2, 3])
        ->orWhereNotIn('user_id', [1, 2, 4])
        ->get();

    return $users;
});
// whereNull / whereNotNull / orWhereNull / orWhereNotNull

Route::get('/whereNull', function () {
    $users = DB::table('users')
        ->whereNull('created_at')
        ->orWhereNull('updated_at')
        ->get();
    return $users;
});
Route::get('/wherenotnull', function () {
    $users = DB::table('users')
        ->whereNotNull('created_at')
        ->orWhereNotNull('updated_at')
        ->get();

    return $users;
});

//whereDate / whereMonth / whereDay / whereYear / whereTime
Route::get('/wheredate', function () {
    // $users = DB::table('users')
    //     ->whereDate('created_at', '2019-11-07 ')
    //     ->get();

    // $users = DB::table('users')
    //     ->whereMonth('created_at', '10')
    //     ->get();

    // $users = DB::table('users')
    //     ->whereDay('created_at', '8')
    //     ->get();

    // $users = DB::table('users')
    //     ->whereYear('created_at', '2020')
    //     ->get();

    $users = DB::table('users')
        ->whereTime('created_at', '=', '11:20:45')
        ->get();

    return $users;
});

// whereColumn / orWhereColumn

Route::get('/wherecolumn', function () {


    // $users = DB::table('users')
    //     ->whereColumn('active', 'amount')
    //     ->get();

    // $users = DB::table('users')
    //     ->whereColumn('active', '>', 'amount')
    //     ->get();

    $users = DB::table('users')
        ->whereColumn([
            ['active', '!=', 'amount'],
            ['created_at', '>', 'updated_at'],
        ])->get();

    return $users;
});

//Parameter Grouping
Route::get('/whereadvanced', function () {
    $users = DB::table('users')
        ->where('name', '=', 'John')
        ->where(function ($query) {
            $query->where('active', '>', 0)
                ->orWhere('id', '=', 5);
        })
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});

//Where Exists Clauses
Route::get('/whereexist', function () {
    $users = DB::table('users')
        ->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('accounts')
                ->whereRaw('accounts.user_id = users.id');
        })
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});

/*
|--------------------------------------------------------------------------
| JSON Where Clauses
|--------------------------------------------------------------------------
|
| Laravel also supports querying JSON column types on databases that provide 
|support for JSON column types.
|LEARNING https://laravel.com/docs/6.x/queries#json-where-clauses
|
*/

//Ordering, Grouping, Limit, & Offset
Route::get('/ordering', function () {
    $users = DB::table('users')
        ->orderBy('id', 'desc')
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});
Route::get('/groupping', function () {
    $accounts = DB::table('accounts')
        ->groupBy('user_id')
        ->get();
    // dd(DB::getQueryLog());
    return $accounts;
});

//latest / oldest
Route::get('/last', function () {
    $user = DB::table('users')
        ->oldest()
        ->get();
    dd(DB::getQueryLog());
    return $user;
});

//inRandomOrder
Route::get('/randomorder', function () {
    $users = DB::table('users')
        ->inRandomOrder()
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});

//groupBy / having
Route::get('/grouphaving', function () {
    $users = DB::table('accounts')
        ->groupBy('user_id', 'created_at')
        ->having('user_id', '>', 10)
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});

//skip = offset / take = limit
Route::get('/skip-take', function () {
    // $users = DB::table('users')->skip(10)->take(5)->get();
    $users = DB::table('users')
        ->offset(10)
        ->limit(5)
        ->get();
    // dd(DB::getQueryLog());
    return $users;
});

//Conditional Clauses
Route::get('/conditional', function () {
    $user_id = 0;

    $users = DB::table('accounts')
        ->when($user_id, function ($query, $user_id) {
            return $query->where('user_id', $user_id);
        })
        ->get();
    // $sortBy = '';

    // $users = DB::table('users')
    //     ->when($sortBy, function ($query, $sortBy) {
    //         return $query->orderBy($sortBy);
    //     }, function ($query) {
    //         return $query->orderBy('name');
    //     })
    //     ->get();
    // dd(DB::getQueryLog());
    return $users;
});

//Inserts
Route::get('/insert', function () {
    DB::table('users')->insert(
        [
            'name' => 'zawzaw',
            'email' => 'zawzaw@gmail.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),

        ]
    );
    // dd(DB::getQueryLog());
    return "Created";
});
//MultiInserts 
Route::get('/multiinsert', function () {
    DB::table('users')->insert(
        [
            [
                'name' => 'naung',
                'email' => 'naung@gmail.com',
                'email_verified_at' => now(),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),

            ],
            [
                'name' => 'mgmg',
                'email' => 'mgmg@gmail.com',
                'email_verified_at' => now(),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),

            ]
        ]
    );
    // dd(DB::getQueryLog());
    return "Created";
});

//Insert Ignore
Route::get('/insertignore', function () {
    DB::table('users')->insertOrIgnore(
        [
            [
                'name' => 'naung',
                'email' => 'naung@gmail.com',
                'email_verified_at' => now(),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),

            ],
            [
                'name' => 'mgmg',
                'email' => 'mgmg@gmail.com',
                'email_verified_at' => now(),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),

            ]
        ]
    );
    dd(DB::getQueryLog());
    return "Created";
});

//Insert AutoIncrement and get Id
Route::get('/insertincrement', function () {
    $id = DB::table('users')->insertGetId(
        [
            'name' => 'aung',
            'email' => 'aung@gmail.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),

        ]
    );
    // dd(DB::getQueryLog());
    return $id;
});


//Update
Route::get('/update', function () {
    $affected = DB::table('users')
        ->where('id', 3)
        ->update(['amount' => 10]);
    // dd(DB::getQueryLog());
    return $affected;
});

//Pesssimitic Locking

Route::get('/lock', function () {

    DB::table('users')->where('amount', '>', 1)->sharedLock()->get();
    // return DB::table('users')->where('amount', '>', 0)->lockForUpdate()->get();
    // dd(DB::getQueryLog());
}); //I don't know

//Debugging
Route::get('/debug', function () {
    // DB::table('users')->where('amount', '>', 0)->dd();

    DB::table('users')->where('amount', '>', 0)->dump();
});

/*
|--------------------------------------------------------------------------
| Paginate
|--------------------------------------------------------------------------
|
|  Laravel's paginator is integrated with the query builder and Eloquent ORM and provides convenient, easy-to-use pagination of database results out of the box
|
*/

//Paginated with groupBy
Route::get('/paginate', function () {
    // $users =  DB::table('users')->groupBy('name')->paginate(5);
    // $users = DB::table('users')->simplePaginate(5);
    $users = DB::table('users')->paginate(5);
    // dd(DB::getQueryLog());
    return $users;
});
//Customizing Url
Route::get('/customize-url', function () {
    $users = App\User::paginate(5);

    // $users->withPath('customize-url'); //change custom url e.g http://localhost:8000/customize-url
    //  $users->appends(['sort' => 'votes'])->links(); //e.g http://localhost:8000/customize-url ?sort=votes
    // $users->fragment('start')->links();  //add fragment  e.g http://localhost:8000/customize-url#start
    return   $users->onEachSide(5)->links();
    // dd(DB::getQueryLog());
    return $users;
});