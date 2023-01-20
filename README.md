<h1 align="center">ACL</h1>

This package provides wrapper of an implementation Access Control List (ACL) Roles-Permissions in repository pattern for Lumen and Laravel.

Getting Started
---

Installation :

```
$ composer require tripteki/laravelphp-acl
```

How to use it :

- Read the instruction for [Lumen](https://spatie.be/docs/laravel-permission/installation-lumen) or [Laravel](https://spatie.be/docs/laravel-permission/installation-laravel).

- Put `Tripteki\ACL\Providers\ACLServiceProvider` to service provider configuration list.

- Publish config file in the root of your project's directory with running and put to register provider :

```
php artisan vendor:publish --tag=tripteki-laravelphp-acl
```

```php
Tripteki\ACL\Providers\ACLServiceProvider::ignoreConfig();
```

- Put `Tripteki\ACL\Traits\RolePermissionTrait` to auth's provider model.

- Put `TargetModel::observe(\Tripteki\ACL\Observers\OwnObserver::class)` in provider.

- Put below to the middleware :

`"role" => \Tripteki\ACL\Http\Middleware\RoleMiddleware::class`<br />
`"permission" => \Tripteki\ACL\Http\Middleware\PermissionMiddleware::class`<br />
`"role_or_permission" => \Tripteki\ACL\Http\Middleware\RoleOrPermissionMiddleware::class`<br />

- Migrate.

```
$ php artisan migrate
```

- Sample :

```php
use Tripteki\ACL\Contracts\Repository\Admin\IACLRoleRepository;
use Tripteki\ACL\Contracts\Repository\Admin\IACLPermissionRepository;
use Tripteki\ACL\Contracts\Repository\IACLRepository;

$roleRepository = app(IACLRoleRepository::class);
$permissionRepository = app(IACLPermissionRepository::class);

// $permissionRepository->rule("edit:users.*"); //
// $permissionRepository->unrule("edit:users.*"); //
// $permissionRepository->get("edit:users.*"); //
// $permissionRepository->all(); //

// $roleRepository->rule("admin"); //
// $roleRepository->rule("user"); //
// $roleRepository->unrule("admin"); //
// $roleRepository->unrule("user"); //
// $roleRepository->get("admin"); //
// $roleRepository->get("user"); //
// $roleRepository->all(); //

// $roleRepository->forRole("admin"); //
// $roleRepository->grant("edit:users.*"); //
// $roleRepository->revoke("edit:users.*"); //
// $roleRepository->ability("edit:users"); //
// $roleRepository->permissions(); //

$repository = app(IACLRepository::class);
// $repository->setUser(...); //
// $repository->getUser(); //

// $repository->grantAs("admin"); //
// $repository->revokeAs("admin"); //
// $repository->is("admin"); //
// $repository->permissions(); //
// $repository->grant("edit:posts.5"); //
// $repository->revoke("edit:posts.5"); //
// $repository->can("edit:posts.5"); //
// $repository->owns(); //
```

Author
---

- Spatie ([@spatie](https://spatie.be))
- Trip Teknologi ([@tripteki](https://linkedin.com/company/tripteki))
- Hasby Maulana ([@hsbmaulana](https://linkedin.com/in/hsbmaulana))
