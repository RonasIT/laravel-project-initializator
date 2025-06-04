use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use RonasIT\Support\Traits\MigrationTrait;
use Illuminate\Support\Facades\Schema;

class AddClerkIdToUsersTable extends Migration
{
    use MigrationTrait;

    public function up()
    {
        if (config('app.env') !== 'testing') {
            Schema::table('users', function (Blueprint $table) {
                $table->string('clerk_id')->unique();
            });
        }
    }

    public function down()
    {
        if (config('app.env') !== 'testing') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_clerk_id_unique');
                $table->dropColumn('clerk_id');
            });
        }
    }
}