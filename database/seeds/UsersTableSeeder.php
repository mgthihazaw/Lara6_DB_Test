<?php

use App\Account;
use App\Post;
use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = factory(User::class, 20)->create();
        $users->each(function ($user) {
            factory(Account::class, 2)->create([
                'user_id' => $user->id,

            ]);
        });
        $users->each(function ($user) {

            factory(Post::class, 3)->create([
                'user_id' => $user->id,

            ]);
        });
    }
}