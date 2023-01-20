<?php

namespace Tripteki\ACL\Providers;

use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;
use Tripteki\ACL\Models\Admin\Role;
use Tripteki\ACL\Models\Admin\Permission;
use Tripteki\Uid\Observers\UniqueIdObserver;
use Tripteki\Repository\Providers\RepositoryServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class ACLServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $repositories =
    [
        \Tripteki\ACL\Contracts\Repository\IACLRepository::class => \Tripteki\ACL\Repositories\Eloquent\ACLRepository::class,
        \Tripteki\ACL\Contracts\Repository\Admin\IACLRoleRepository::class => \Tripteki\ACL\Repositories\Eloquent\Admin\ACLRoleRepository::class,
        \Tripteki\ACL\Contracts\Repository\Admin\IACLPermissionRepository::class => \Tripteki\ACL\Repositories\Eloquent\Admin\ACLPermissionRepository::class,
    ];

    /**
     * @var string
     */
    public const SUPERUSER = "superuser";

    /**
     * @var bool
     */
    public static $loadConfig = true;

    /**
     * @var bool
     */
    public static $runsMigrations = true;

    /**
     * @return bool
     */
    public static function shouldLoadConfig()
    {
        return static::$loadConfig;
    }

    /**
     * @return bool
     */
    public static function shouldRunMigrations()
    {
        return static::$runsMigrations;
    }

    /**
     * @return void
     */
    public static function ignoreConfig()
    {
        static::$loadConfig = false;
    }

    /**
     * @return void
     */
    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;
    }

    /**
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->dataEventListener();

        $this->registerConfigs();
        $this->registerPublishers();
        $this->registerMigrations();
        $this->registerSuperuser();
    }

    /**
     * @return void
     */
    protected function registerSuperuser()
    {
        Gate::before(function ($user, $ability) {

            return $user->hasRole(ACLServiceProvider::SUPERUSER) ? true : null;
        });

        // Gate::after(function ($user, $ability) { //

            // return $user->hasRole(ACLServiceProvider::SUPERUSER); //
        // }); //
    }

    /**
     * @return void
     */
    protected function registerConfigs()
    {
        if (static::shouldLoadConfig()) {

            $this->app["config"]->set("permission", []);
            $this->mergeConfigFrom(__DIR__."/../../config/acl.php", "permission");
        }

        $this->app->bind(PermissionContract::class, function ($app) {

            $config = $app->config["permission.models"];

            return $app->make($config["permission"]);
        });

        $this->app->bind(RoleContract::class, function ($app) {

            $config = $app->config["permission.models"];

            return $app->make($config["role"]);
        });
    }

    /**
     * @return void
     */
    protected function registerMigrations()
    {
        if ($this->app->runningInConsole() && static::shouldRunMigrations()) {

            $this->loadMigrationsFrom(__DIR__."/../../database/migrations");
        }
    }

    /**
     * @return void
     */
    protected function registerPublishers()
    {
        $this->publishes(
        [
            __DIR__."/../../config/acl.php" => config_path("permission.php"),
        ],

        "tripteki-laravelphp-acl");

        if (! static::shouldRunMigrations()) {

            $this->publishes(
            [
                __DIR__."/../../database/migrations" => database_path("migrations"),
            ],

            "tripteki-laravelphp-acl-migrations");
        }
    }

    /**
     * @return void
     */
    public function dataEventListener()
    {
        Role::observe(UniqueIdObserver::class);
        Permission::observe(UniqueIdObserver::class);
    }
};
