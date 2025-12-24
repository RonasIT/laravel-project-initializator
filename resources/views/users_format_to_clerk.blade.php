use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RonasIT\Support\Traits\MigrationTrait;

return new class extends Migration
{
    use MigrationTrait;

    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('clerk_id')->unique();

            $table->dropColumn('password');
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->dropRememberToken();

            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });

        Schema::dropIfExists('password_reset_tokens');
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('clerk_id');

            $table->string('password');
            $table->string('name')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }
};
