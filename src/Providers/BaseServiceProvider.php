<?php

namespace SultanovSolutions\LaravelBase\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BaseServiceProvider extends ServiceProvider
{

    protected string $routes_dir = 'Routes';

    public function boot()
    {
        $this->loadRoutes();
    }

    public function register()
    {
        $this->loadConfigs();
    }

    protected function getCurrentDir(string $dir = null): string
    {
        if (!$dir)
            return dirname((new \ReflectionClass(get_called_class()))->getFileName());

        return dirname((new \ReflectionClass(get_called_class()))->getFileName()) . DIRECTORY_SEPARATOR . $dir;
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

        $config_files = collect(scandir($this->getCurrentDir('Configs')))
            ->filter(fn($r) => !in_array($r, ['.', '..']))
            ->toArray();

        foreach ($config_files as $config_file)
            $this->loadConfigPath($config_file);
    }

    private function loadConfigPath($path)
    {
        if (is_dir($this->getCurrentDir('Configs' . DIRECTORY_SEPARATOR . $path )))
        {
            $config_path = str($path)->trim()->toString();

            $config_files = collect(scandir($this->getCurrentDir('Configs' . DIRECTORY_SEPARATOR . $config_path)))
                ->filter(fn($r) => !in_array($r, ['.', '..']))
                ->toArray();

            foreach ($config_files as $config_file)
                $this->loadConfigPath($config_path . DIRECTORY_SEPARATOR . $config_file, true );
        }else{
            $config_path = str($path)->trim()->toString();

            if( $oldConfig = config(str($config_path)->remove('.php')->replace(DIRECTORY_SEPARATOR, '.')->toString()) ){
                config()
                    ->set(
                        str($config_path)->remove('.php')->replace(DIRECTORY_SEPARATOR, '.')->toString(),
                        collect(require $this->getCurrentDir('Configs' . DIRECTORY_SEPARATOR . $config_path ))->merge($oldConfig)->toArray()
                    );
            }else{
                config()
                    ->set(
                        str($config_path)->remove('.php')->replace(DIRECTORY_SEPARATOR, '.')->toString(),
                        require $this->getCurrentDir('Configs' . DIRECTORY_SEPARATOR . $config_path )
                    );
            }
        }
    }
}
