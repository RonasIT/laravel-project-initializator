<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RonasIT\Support\Traits\MigrationTrait;

class AddTelescopeAdmin extends Migration
{
    use MigrationTrait;

    public function up(): void
    {
        if (!App::environment('testing')) {
            DB::table('admins')->insert([
                'email' => 'telescope_mail@mail.com',
                'password' => Hash::make('654321'),
            ]);
        }
    }

    public function down(): void
    {
        if (!App::environment('testing')) {
            DB::table('admins')
                ->where('email', 'telescope_mail@mail.com')
                ->delete();
        }
    }
}
