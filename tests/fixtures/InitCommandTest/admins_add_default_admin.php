<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RonasIT\Support\Traits\MigrationTrait;

class AddDefaultAdmin extends Migration
{
    use MigrationTrait;

    public function up(): void
    {
        if (!App::environment('testing')) {
            DB::table('admins')->insert([
                'email' => 'mail@mail.com',
                'password' => Hash::make('123456'),
            ]);
        }
    }

    public function down(): void
    {
        if (!App::environment('testing')) {
            DB::table('admins')
                ->where('email', 'mail@mail.com')
                ->delete();
        }
    }
}
