<?php

namespace Tripteki\ACL\Repositories\Eloquent\Admin;

use Error;
use Exception;
use Illuminate\Support\Facades\DB;
use Tripteki\ACL\Events\Granting;
use Tripteki\ACL\Events\Granted;
use Tripteki\ACL\Events\Revoking;
use Tripteki\ACL\Events\Revoked;
use Spatie\Permission\Contracts\Role as RoleModel;
use Tripteki\ACL\Contracts\Repository\Admin\IACLRoleRepository;
use Tripteki\RequestResponseQuery\QueryBuilder;

class ACLRoleRepository implements IACLRoleRepository
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $role;

    /**
     * @param string $role
     * @return void
     */
    public function forRole($role)
    {
        $this->setRole($this->get($role));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $role
     * @return void
     */
    protected function setRole(\Illuminate\Database\Eloquent\Model $role)
    {
        $this->role = $role;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getRole()
    {
        return $this->role;
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

        $content = QueryBuilder::for(app(RoleModel::class)->query())->
        defaultSort("name")->
        allowedSorts([ "name", "guard_name", ])->
        allowedFilters([ "name", "guard_name", ])->
        paginate($limit, [ "*", ], "current_page", $current_page)->appends(empty($querystring) ? request()->query() : $querystringed);

        return $content;
    }

    /**
     * @param int|string $identifier
     * @param array $querystring|[]
     * @return mixed
     */
    public function get($identifier, $querystring = [])
    {
        $querystringed =
        [
            "limit" => $querystring["limit"] ?? request()->query("limit", 10),
            "current_page" => $querystring["current_page"] ?? request()->query("current_page", 1),
        ];
        extract($querystringed);

        $content = app(RoleModel::class)->findByName($identifier);
        $content = $content->setRelation("permissions",
            QueryBuilder::for($content->permissions())->
            defaultSort("name")->
            allowedSorts([ "name", "guard_name", ])->
            allowedFilters([ "name", "guard_name", ])->
            paginate($limit, [ "*", ], "current_page", $current_page)->appends(empty($querystring) ? request()->query() : $querystringed));
        $content = $content->loadCount("permissions");

        return $content;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create($data)
    {
        $content = null;

        DB::beginTransaction();

        try {

            $content = app(RoleModel::class)->create($data);

            DB::commit();

        } catch (Exception $exception) {

            DB::rollback();
        }

        return $content;
    }

    /**
     * @param int|string $identifier
     * @return mixed
     */
    public function delete($identifier)
    {
        $content = app(RoleModel::class)->findByName($identifier);

        DB::beginTransaction();

        try {

            $content->delete();

            DB::commit();

        } catch (Exception $exception) {

            DB::rollback();
        }

        return $content;
    }

    /**
     * @param string $role
     * @return mixed
     */
    public function rule($role)
    {
        return $this->create([ "name" => $role, ]);
    }

    /**
     * @param string $role
     * @return mixed
     */
    public function unrule($role)
    {
        return $this->delete($role);
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

            $content = $this->role->givePermissionTo($permission);

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

            $content = $this->role->revokePermissionTo($permission);

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
    public function ability($permission)
    {
        return $this->role->hasPermissionTo($permission);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function permissions()
    {
        return $this->role->permissions()->get();
    }
};
