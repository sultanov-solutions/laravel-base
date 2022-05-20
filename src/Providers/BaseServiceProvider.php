<?php

namespace SultanovSolutions\LaravelBase\Providers;

use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BaseServiceProvider extends ServiceProvider
{
    protected ?string $configs_dir = 'Configs';

    protected ?string $routes_dir = 'Routes';

    protected ?string $vendor_dir = '';

    protected bool $envExist = false;

    public function __construct($app)
    {
        parent::__construct($app);

        if (file_exists($this->getCurrentDir('../.env')))
            $this->envExist = true;

        $this->custom_construct();
    }

    public function custom_construct(): void
    {
    }

    public function boot()
    {
        $this->loadRoutes();
        $this->loadMigrations();
        $this->loadSeeders();
        $this->onBoot();
    }

    public function register()
    {
        $this->loadConfigs();
        $this->onRegister();
    }

    protected function getCurrentDir(string $dir = null): string
    {
        $root_dir = dirname((new \ReflectionClass(get_called_class()))->getFileName());


        if ($this->vendor_dir) {
            if (
                is_dir(base_path('vendor' . DIRECTORY_SEPARATOR . $this->vendor_dir)) &&
                is_file(base_path('vendor' . DIRECTORY_SEPARATOR . $this->vendor_dir . DIRECTORY_SEPARATOR . 'composer.json'))
            ) {
                $composer_json = json_decode(File::get(base_path('vendor' . DIRECTORY_SEPARATOR . $this->vendor_dir . DIRECTORY_SEPARATOR . 'composer.json')), 1);
                $project_folder = array_pop($composer_json['autoload']['psr-4']);

                if (is_dir(base_path('vendor' . DIRECTORY_SEPARATOR . $this->vendor_dir . DIRECTORY_SEPARATOR . $project_folder)))
                    $root_dir = base_path('vendor' . DIRECTORY_SEPARATOR . $this->vendor_dir . DIRECTORY_SEPARATOR . $project_folder);
            }
        }

        if (!$dir)
            return $root_dir;

        return $root_dir . DIRECTORY_SEPARATOR . $dir;
    }

    private function loadRoutes()
    {
        $ROUTES_DIR = $this->getCurrentDir('/' . $this->routes_dir);

        if (is_dir($ROUTES_DIR)) {
            $routes = collect(scandir($ROUTES_DIR))
                ->filter(fn($r) => !in_array($r, ['.', '..']) && str($r)->endsWith('.php'))
                ->toArray();

            foreach ($routes as $route) {
                if ($route === 'api.php') {
                    Route::prefix('api')
                        ->middleware('api')
                        ->group($ROUTES_DIR . '/api.php');
                }
                if ($route === 'web.php') {
                    Route::middleware('web')
                        ->group($ROUTES_DIR . '/web.php');
                }
            }
        }
    }

    private function loadConfigs()
    {
        if (is_dir($this->getCurrentDir($this->configs_dir))) {
            $config_files = collect(scandir($this->getCurrentDir($this->configs_dir)))
                ->filter(fn($r) => !in_array($r, ['.', '..']))
                ->toArray();

            foreach ($config_files as $config_file)
                $this->loadConfigPath($config_file);
        }
    }

    private function loadConfigPath($path)
    {
        if (is_dir($this->getCurrentDir($this->configs_dir . DIRECTORY_SEPARATOR . $path))) {
            $config_path = str($path)->trim()->toString();

            $config_files = collect(scandir($this->getCurrentDir($this->configs_dir . DIRECTORY_SEPARATOR . $config_path)))
                ->filter(fn($r) => !in_array($r, ['.', '..']))
                ->toArray();

            foreach ($config_files as $config_file)
                $this->loadConfigPath($config_path . DIRECTORY_SEPARATOR . $config_file, true);
        } else {
            $config_path = str($path)->trim()->toString();

            if ($oldConfig = config(str($config_path)->remove('.php')->replace(DIRECTORY_SEPARATOR, '.')->toString())) {
                config()
                    ->set(
                        str($config_path)->remove('.php')->replace(DIRECTORY_SEPARATOR, '.')->toString(),
                        collect(require $this->getCurrentDir($this->configs_dir . DIRECTORY_SEPARATOR . $config_path))->merge($oldConfig)->toArray()
                    );
            } else {
                config()
                    ->set(
                        str($config_path)->remove('.php')->replace(DIRECTORY_SEPARATOR, '.')->toString(),
                        require $this->getCurrentDir($this->configs_dir . DIRECTORY_SEPARATOR . $config_path)
                    );
            }
        }
    }

    private function publishFiles(string $path, string $target_dir){
        if ($path){
            $path_key = str($path)->replace('/', '-')->slug();

            $composer_json = json_decode(File::get($this->getCurrentDir('../composer.json')), 1);
            $package_name = str($composer_json['name'])->replace('/', '-')->slug()->toString();

            if ($this->app->runningInConsole()) {
                if (is_dir($this->getCurrentDir('Database/' . $path))){
                    $migrationsFiles = collect(scandir($this->getCurrentDir('Database/' . $path)))->filter(fn($f) => !in_array($f, ['.', '..']))->toArray();

                    if (is_array($migrationsFiles) && count($migrationsFiles) )
                        $this->publishes([$this->getCurrentDir('Database/' . $path) => $target_dir], $package_name . '-' . $path_key);
                }
            }
        }

    }

    private function loadMigrations()
    {
        $this->publishFiles('migrations', database_path('migrations'));
    }

    private function loadSeeders()
    {
        $this->publishFiles('seeders', database_path('seeders'));
    }

    /**
     * @throws Exception
     */
    public function env($key, $default = null): ?string
    {
        if ($this->envExist)
        {
            return cache()->remember('config-'.$key, now()->addMinutes(20), function () use ($key, $default){
                $env_str = str(File::get($this->getCurrentDir('../.env')));

                if ($env_str->contains($key))
                    $val = collect($env_str->explode("\r"))->filter(fn($val) => str(str($val)->explode('=')[0])->trim()->toString() === $key)->first();

                if ($val)
                    return str($val)->trim()->after('=')->trim()->toString();

                return $default;
            });
        }

        if(!App::runningInConsole())
            throw new Exception('Env file not found');

        return null;
    }

    public function onBoot(): void
    {
    }

    public function onRegister(): void
    {
    }
}
