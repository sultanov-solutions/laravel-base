<?php

namespace SultanovSolutions\LaravelBase\Traits;

trait ProviderOptions
{
    protected ?string $configs_dir = 'Configs';

    protected ?string $routes_dir = 'Routes';

    protected ?string $vendor_dir = '';

    protected bool $envExist = false;

    protected int $envCacheTime = 20;

    private bool $loadViews = true;

    private bool $loadSeeders = true;

    private bool $loadMigrations = true;

    private bool $loadConfigs = true;

    private bool $loadRoutes = true;

    private bool $loadCommands = true;
}
