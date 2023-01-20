<?php

namespace Tripteki\ACL\Repositories\Eloquent\Admin;

use Error;
use Exception;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Contracts\Permission as PermissionModel;
use Tripteki\ACL\Contracts\Repository\Admin\IACLPermissionRepository;
use Tripteki\RequestResponseQuery\QueryBuilder;

class ACLPermissionRepository implements IACLPermissionRepository
{
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

        $content = QueryBuilder::for(app(PermissionModel::class)->query())->
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

        $content = app(PermissionModel::class)->findByName($identifier);
        $content = $content->setRelation("roles",
            QueryBuilder::for($content->roles())->
            defaultSort("name")->
            allowedSorts([ "name", "guard_name", ])->
            allowedFilters([ "name", "guard_name", ])->
            paginate($limit, [ "*", ], "current_page", $current_page)->appends(empty($querystring) ? request()->query() : $querystringed));
        $content = $content->loadCount("roles");

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

            $content = app(PermissionModel::class)->create($data);

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
        $content = app(PermissionModel::class)->findByName($identifier);

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
     * @param string $permission
     * @return mixed
     */
    public function rule($permission)
    {
        return $this->create([ "name" => $permission, ]);
    }

    /**
     * @param string $permission
     * @return mixed
     */
    public function unrule($permission)
    {
        return $this->delete($permission);
    }
};
