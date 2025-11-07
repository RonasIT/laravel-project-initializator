<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        if (!App::environment('testing')) {
            DB::table('users')->insert([
                'name' => 'Admin',
                'email' => 'admin@my-app.com',
                'password' => Hash::make('123456'),
                'role_id' => 1,
            ]);
        }
    }

    public function down(): void
    {
        if (!App::environment('testing')) {
            DB::table('users')
                ->where('email', 'admin@my-app.com')
                ->delete();
        }
    }
};
