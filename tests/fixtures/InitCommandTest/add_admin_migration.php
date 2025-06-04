<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RonasIT\Support\Traits\MigrationTrait;

class AddAdminUser extends Migration
{
    use MigrationTrait;

    public function up()
    {
        if (config('app.env') !== 'testing') {
            DB::table('admins')->insert([
                'name' => 'TestAdmin',
                'email' => 'mail@mail.com',
                'password' => Hash::make('123456'),
            ]);
        }
    }

    public function down()
    {
        if (config('app.env') !== 'testing') {
            DB::table('admins')
                ->where('email', 'mail@mail.com')
                ->delete();
        }
    }
}