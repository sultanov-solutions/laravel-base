<?php

namespace SultanovSolutions\LaravelBase\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BaseServiceProvider extends ServiceProvider
{
    protected string $routes_dir = 'Routes';

    protected string $configs_dir = 'Configs/requests';

    protected function getCurrentDir(): string
    {
        return dirname((new \ReflectionClass(get_called_class()))->getFileName());
    }

    private function loadRoutes()
    {
        $ROUTES_DIR = $this->getCurrentDir() . '/' . $this->routes_dir;

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
        $CONFIG_DIR = $this->getCurrentDir() . '/' . $this->configs_dir;

        if (is_dir($CONFIG_DIR)) {
            $config_files = collect(scandir($CONFIG_DIR))
                ->filter(fn($r) => !in_array($r, ['.', '..']) && str($r)->endsWith('.php'))
                ->toArray();

            foreach ($config_files as $config_file)
                config()
                    ->set(
                        'requests.' . str($config_file)->remove('.php')->trim()->toString(),
                        require_once $CONFIG_DIR . '/' . $config_file
                    );
        }
    }

    public function boot()
    {
        $this->loadRoutes();
    }

    public function register()
    {
        $this->loadConfigs();
    }
}
