<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RonasIT\Support\Traits\MigrationTrait;

class AdminsCreateTable extends Migration
{
    use MigrationTrait;

    public function up()
    {
        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->increments('id');
                $table->string('email')->unique();
                $table->string('password');
            });
        }

        if (!App::environment('testing')) {
            DB::table('admins')->insert([
                'email' => 'mail@mail.com',
                'password' => Hash::make('123456'),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('admins');
    }
}
