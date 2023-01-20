<?php

namespace Tripteki\ACL\Observers;

use Illuminate\Support\Str;
use Tripteki\ACL\Contracts\Repository\Admin\IACLRoleRepository;
use Tripteki\ACL\Contracts\Repository\Admin\IACLPermissionRepository;
use Tripteki\ACL\Contracts\Repository\IACLRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class OwnObserver
{
    use AuthorizesRequests;

    /**
     * @var array
     */
    protected $ables;

    /**
     * @var \Tripteki\ACL\Contracts\Repository\Admin\IACLRoleRepository
     */
    protected $role;

    /**
     * @var \Tripteki\ACL\Contracts\Repository\Admin\IACLPermissionRepository
     */
    protected $permission;

    /**
     * @var \Tripteki\ACL\Contracts\Repository\IACLRepository
     */
    protected $acl;

    /**
     * @param \Tripteki\ACL\Contracts\Repository\Admin\IACLRoleRepository $role
     * @param \Tripteki\ACL\Contracts\Repository\Admin\IACLPermissionRepository $permission
     * @param \Tripteki\ACL\Contracts\Repository\IACLRepository $acl
     * @return void
     */
    public function __construct(IACLRoleRepository $role, IACLPermissionRepository $permission, IACLRepository $acl)
    {
        $this->role = $role;
        $this->permission = $permission;
        $this->acl = $acl;
        $this->ables = collect($this->resourceAbilityMap())->only(config("permission.own_resources"))->toArray();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string
     */
    protected function id($model)
    {
        $class = Str::plural(Str::replace("\\", "_", Str::lower(get_class($model))));

        return $class.".".$model->{$model->getKeyName()};
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function created($model)
    {
        if (Auth::check()) {

            $this->acl->setUser(Auth::user());

            $id = $this->id($model);

            foreach ($this->ables as $able) {

                $action = $able.".".$id;

                $this->permission->rule($action);
                $this->acl->grant($action);
            }
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function deleted($model)
    {
        if (Auth::check()) {

            $this->acl->setUser(Auth::user());

            $id = $this->id($model);

            foreach ($this->ables as $able) {

                $action = $able.".".$id;

                $this->acl->revoke($action);
                $this->permission->unrule($action);
            }
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function restored($model)
    {
        $this->created($model);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function forceDeleted($model)
    {
        $this->deleted($model);
    }
};
