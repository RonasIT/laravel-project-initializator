<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RonasIT\Support\Traits\MigrationTrait;

class UsersRemovePasswordMakeNullableEmailAndName extends Migration
{
    use MigrationTrait;

    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->string('email')->nullable()->change();
            $table->string('name')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table){
            $table->string('password');
            $table->string('email')->nullable(false)->change();
            $table->string('name')->nullable(false)->change();
        });
    }
}