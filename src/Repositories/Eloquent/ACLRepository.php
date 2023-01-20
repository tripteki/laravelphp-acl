<?php

namespace Tripteki\ACL\Repositories\Eloquent;

use Error;
use Exception;
use Illuminate\Support\Facades\DB;
use Tripteki\Repository\AbstractRepository;
use Tripteki\ACL\Events\Granting;
use Tripteki\ACL\Events\Granted;
use Tripteki\ACL\Events\Revoking;
use Tripteki\ACL\Events\Revoked;
use Tripteki\ACL\Contracts\Repository\IACLRepository;
use Tripteki\ACL\Contracts\Repository\Admin\IACLRoleRepository;
use Tripteki\ACL\Contracts\Repository\Admin\IACLPermissionRepository;
use Tripteki\RequestResponseQuery\QueryBuilder;

class ACLRepository extends AbstractRepository implements IACLRepository
{
    /**
     * @var \Tripteki\ACL\Contracts\Repository\Admin\IACLRoleRepository
     */
    protected $role;

    /**
     * @var \Tripteki\ACL\Contracts\Repository\Admin\IACLPermissionRepository
     */
    protected $permission;

    /**
     * @param \Tripteki\ACL\Contracts\Repository\Admin\IACLRoleRepository $role
     * @param \Tripteki\ACL\Contracts\Repository\Admin\IACLPermissionRepository $permission
     * @return void
     */
    public function __construct(IACLRoleRepository $role, IACLPermissionRepository $permission)
    {
        $this->role = $role;
        $this->permission = $permission;
    }

    /**
     * @param array $querystring|[]
     * @return mixed
     */
    public function all($querystring = [])
    {
        $querystringed =
        [
            "limit" => $querystring["limit"] ?? request()->query("limit", 10),
            "current_page" => $querystring["current_page"] ?? request()->query("current_page", 1),
        ];
        extract($querystringed);

        $content = $this->user;
        $content = $content->setRelation("roles",
            QueryBuilder::for($content->roles())->
            defaultSort("name")->
            allowedSorts([ "name", "guard_name", ])->
            allowedFilters([ "name", "guard_name", ])->
            paginate($limit, [ "*", ], "current_page", $current_page)->appends(empty($querystring) ? request()->query() : $querystringed));
        $content = $content->loadCount("roles");

        return collect($content)->only([ "roles_count", "roles", ]);
    }

    /**
     * @param string|int $role
     * @return mixed
     */
    public function grantAs($role)
    {
        $content = null;

        DB::beginTransaction();

        try {

            $content = $this->user->assignRole($this->role->get($role));

            DB::commit();

            event(new Granted($content));

        } catch (Exception $exception) {

            DB::rollback();
        }

        return $content;
    }

    /**
     * @param string|int $role
     * @return mixed
     */
    public function revokeAs($role)
    {
        $content = null;

        DB::beginTransaction();

        try {

            $content = $this->user->removeRole($this->role->get($role));

            DB::commit();

            event(new Revoked($content));

        } catch (Exception $exception) {

            DB::rollback();
        }

        return $content;
    }

    /**
     * @param string $role
     * @return bool
     */
    public function is($role)
    {
        return $this->user->hasRole($role);
    }

    /**
     * @param string|int $permission
     * @return mixed
     */
    public function grant($permission)
    {
        $content = null;

        DB::beginTransaction();

        try {

            $content = $this->user->givePermissionTo($this->permission->get($permission));

            DB::commit();

            event(new Granted($content));

        } catch (Exception $exception) {

            DB::rollback();
        }

        return $content;
    }

    /**
     * @param string|int $permission
     * @return mixed
     */
    public function revoke($permission)
    {
        $content = null;

        DB::beginTransaction();

        try {

            $content = $this->user->revokePermissionTo($this->permission->get($permission));

            DB::commit();

            event(new Revoked($content));

        } catch (Exception $exception) {

            DB::rollback();
        }

        return $content;
    }

    /**
     * @param string $permission
     * @return bool
     */
    public function can($permission)
    {
        return $this->user->can($permission);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function permissions()
    {
        return $this->user->getPermissionsViaRoles();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function owns()
    {
        return $this->user->getDirectPermissions();
    }
};
