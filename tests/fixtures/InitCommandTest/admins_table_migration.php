<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RonasIT\Support\Traits\MigrationTrait;

class AddAdminsTable extends Migration
{
    use MigrationTrait;

    public function up()
    {
        if (config('app.env') !== 'testing') {
            Schema::create('admins', function (Blueprint $table) {
                $table->increments('id');
                $table->string('email')->unique();
                $table->string('password');
            });

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
            Schema::dropIfExists('admins');
            
            DB::table('admins')
                ->where('email', 'mail@mail.com')
                ->delete();
        }
    }
}