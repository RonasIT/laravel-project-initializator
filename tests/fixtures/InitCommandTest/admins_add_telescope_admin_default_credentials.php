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
            DB::table('admins')->insert([
                'email' => 'admin.telescope@my-app.com',
                'password' => Hash::make('654321'),
            ]);
        }
    }

    public function down(): void
    {
        if (!App::environment('testing')) {
            DB::table('admins')
                ->where('email', 'admin.telescope@my-app.com')
                ->delete();
        }
    }
};
