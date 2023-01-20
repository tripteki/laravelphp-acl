<?php

use Tripteki\ACL\Traits\RolePermissionTrait;
use Tripteki\ACL\Contracts\Repository\IACLRepository;
use Tripteki\Helpers\Contracts\AuthModelContract;
use Illuminate\Support\Facades\Auth;

if (! function_exists("accesses"))
{
    /**
     * @param \Illuminate\Database\Eloquent\Model|null $user
     * @return array
     */
    function accesses(\Illuminate\Database\Eloquent\Model|null $user = null)
    {
        $accesses = [];

        $class = get_class(app(AuthModelContract::class));
        $repository = app(IACLRepository::class);

        if ($user) {

            $repository->setUser($user);

        } else {

            if (Auth::check()) {

                $repository->setUser(Auth::user());
            }
        }

        if ($repository->getUser() instanceof $class && in_array(RolePermissionTrait::class, class_uses($class))) {

            $accesses = array_merge($repository->permissions()->toArray(), $repository->owns()->toArray());
        }

        return $accesses;
    };
}
