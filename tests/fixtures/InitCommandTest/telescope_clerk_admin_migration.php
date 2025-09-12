<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RonasIT\Support\Traits\MigrationTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddTelescopeAdmin extends Migration
{
    use MigrationTrait;

    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password');
        });

        if (!App::environment('testing')) {
            DB::table('admins')->insert([
                'email' => 'telescope_mail@mail.com',
                'password' => Hash::make('654321'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');

        if (!App::environment('testing')) {
            DB::table('admins')
                ->where('email', 'telescope_mail@mail.com')
                ->delete();
        }
    }
}
