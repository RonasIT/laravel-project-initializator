<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RonasIT\Support\Traits\MigrationTrait;

class AddDefaultUser extends Migration
{
    use MigrationTrait;

    public function up(): void
    {
        if (!App::environment('testing')) {
            DB::table('users')->insert([
                'name' => 'TestAdmin',
                'email' => 'mail@mail.com',
                'password' => Hash::make('123456'),
                'role_id' => 1,
            ]);
        }
    }

    public function down(): void
    {
        if (!App::environment('testing')) {
            DB::table('users')
                ->where('email', 'mail@mail.com')
                ->delete();
        }
    }
}
