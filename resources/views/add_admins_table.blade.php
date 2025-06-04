use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use RonasIT\Support\Traits\MigrationTrait;
use Illuminate\Support\Facades\Schema;

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
        }
    }

    public function down()
    {
        if (config('app.env') !== 'testing') {
            Schema::dropIfExists('admins');
        }
    }
}