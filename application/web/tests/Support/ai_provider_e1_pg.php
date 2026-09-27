<?php

use App\Domains\Academic\AI\Models\AiProviderCredential;
use App\Domains\Academic\AI\Services\AiProviderConfigurationService;
use App\Models\User;
use Illuminate\Foundation\Bootstrap\BootProviders;
use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Bootstrap\RegisterFacades;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Input\ArrayInput;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$mode = $argv[1] ?? null;
$database = getenv('DB_DATABASE') ?: '';
$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '';
$username = getenv('DB_USERNAME') ?: '';

if ($database !== 'imtaq_e1_disposable') {
    fwrite(STDERR, "DIRECT_DB_TARGET_GUARD=FAIL expected=imtaq_e1_disposable actual={$database}\n");
    exit(20);
}

if ($mode === 'guard') {
    $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $username, getenv('DB_PASSWORD') ?: '');
    $row = $pdo->query('select current_database() as database_name, current_user as user_name')->fetch(PDO::FETCH_ASSOC);
    if (($row['database_name'] ?? null) !== 'imtaq_e1_disposable') {
        fwrite(STDERR, "DIRECT_DB_TARGET_GUARD=FAIL\n");
        exit(21);
    }
    echo json_encode($row, JSON_THROW_ON_ERROR).PHP_EOL;
    exit(0);
}

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->bootstrapWith([
    LoadEnvironmentVariables::class,
    LoadConfiguration::class,
    HandleExceptions::class,
    RegisterFacades::class,
    RegisterProviders::class,
    BootProviders::class,
]);
$app['config']->set('database.default', 'pgsql');
$app['config']->set('database.connections.pgsql.host', $host);
$app['config']->set('database.connections.pgsql.port', $port);
$app['config']->set('database.connections.pgsql.database', $database);
$app['config']->set('database.connections.pgsql.username', $username);
$app['config']->set('database.connections.pgsql.password', getenv('DB_PASSWORD') ?: '');
$app['config']->set('database.connections.pgsql.url', null);
DB::purge('pgsql');
DB::reconnect('pgsql');

$resolved = DB::connection('pgsql')->getDatabaseName();
if ($resolved !== 'imtaq_e1_disposable') {
    fwrite(STDERR, "LARAVEL_RESOLVED_DB_TARGET_GUARD=FAIL actual={$resolved}\n");
    exit(22);
}

if ($mode === 'laravel-guard') {
    echo json_encode(['database_name' => $resolved, 'driver' => DB::connection('pgsql')->getDriverName()], JSON_THROW_ON_ERROR).PHP_EOL;
    exit(0);
}

if ($mode === 'migrate') {
    exit($app->handleCommand(new ArrayInput(['command' => 'migrate', '--database' => 'pgsql', '--force' => true])));
}

if ($mode === 'seed') {
    $user = User::query()->create(['name' => 'E1 synthetic worker', 'email' => 'e1-worker@example.test', 'password' => 'synthetic-only']);
    $credential = AiProviderCredential::query()->create([
        'provider' => 'openai',
        'label' => 'E1 synthetic credential',
        'encrypted_secret' => 'sk-e1-synthetic-only',
        'secret_last4' => 'only',
        'status' => 'VERIFIED',
    ]);
    echo json_encode(['user_id' => $user->id, 'credential_id' => $credential->id], JSON_THROW_ON_ERROR).PHP_EOL;
    exit(0);
}

if ($mode === 'worker') {
    $user = User::query()->findOrFail($argv[2] ?? '');
    $credential = AiProviderCredential::query()->findOrFail($argv[3] ?? '');
    $startFile = $argv[4] ?? '';
    while (! is_file($startFile)) {
        usleep(1000);
    }
    Http::fake(['*/v1/models' => Http::response(['data' => [['id' => 'gpt-e1-synthetic']]], 200)]);
    try {
        $configuration = app(AiProviderConfigurationService::class)->createConfiguration($user, $credential, 'gpt-e1-synthetic', 800);
        echo json_encode(['result' => 'CREATED', 'configuration_id' => $configuration->id], JSON_THROW_ON_ERROR).PHP_EOL;
        exit(0);
    } catch (ValidationException $exception) {
        echo json_encode(['result' => 'CANONICAL_REJECTION', 'errors' => $exception->errors()], JSON_THROW_ON_ERROR).PHP_EOL;
        exit(0);
    }
}

if ($mode === 'count') {
    echo (string) DB::table('ai_provider_configurations')->where([
        'provider' => 'openai',
        'model' => 'gpt-e1-synthetic',
        'max_output_tokens' => 800,
        'status' => 'DRAFT',
    ])->count().PHP_EOL;
    exit(0);
}

fwrite(STDERR, "Unknown mode\n");
exit(2);
