use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RonasIT\Support\Traits\MigrationTrait;
@if (!$isAdminsCreated)
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
@endif

class Add{{ ucfirst($credentialName) }}Admin extends Migration
{
    use MigrationTrait;

    public function up(): void
    {
        @if (!$isAdminsCreated)
Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password');
        });

        @endif
if (!App::environment('testing')) {
            DB::table('admins')->insert([
                'email' => '{{ $email }}',
                'password' => Hash::make('{{ $password }}'),
            ]);
        }
    }

    public function down(): void
    {
        @if (!$isAdminsCreated)
Schema::dropIfExists('admins');

        @endif
if (!App::environment('testing')) {
            DB::table('admins')
                ->where('email', '{{ $email }}')
                ->delete();
        }
    }
}
