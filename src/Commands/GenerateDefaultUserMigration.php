<?php

namespace Ronas\LaravelProjectInitializator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class GenerateDefaultUserMigration extends Command
{
    protected $signature = 'generate:default-user-migration {--credentials=}';

    protected $description = 'Generate a migration file for adding default user';

    public function handle()
    {
        $migrationContent = $this->generateMigrationContent(json_decode($this->option('credentials'), true));

        $fileName = Carbon::now()->format('Y_m_d_His') . '_add_default_user.php';

        file_put_contents(database_path("migrations/{$fileName}"), $migrationContent);

        $this->info("Migration file {$fileName} generated successfully!");
    }

    protected function generateMigrationContent(array $credentials)
    {
        $name = $credentials['name'];
        $email = $credentials['email'];
        $password = Hash::make($credentials['password']);
        $role_id = $credentials['role_id'];

        return <<<EOT
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AddDefaultUser extends Migration
{
    public function up()
    {
        if (config('app.env') !== 'testing') {
            DB::table('users')->insert([
                'name' => '$name',
                'email' => '$email',
                'password' => '$password',
                'role_id' => $role_id
            ]);
        }
    }

    public function down()
    {
        if (config('app.env') !== 'testing') {
            DB::table('users')
                ->where('email', '$email')
                ->delete();
        }
    }
}
EOT;
    }
}
