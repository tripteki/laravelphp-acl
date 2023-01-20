<?php

use Illuminate\Support\Str;

return [

    "models" => [

        "role" => Tripteki\ACL\Models\Admin\Role::class,
        "permission" => Tripteki\ACL\Models\Admin\Permission::class,
    ],

    "table_names" => [

        "roles" => "acl_roles",
        "permissions" => "acl_permissions",

        "model_has_permissions" => "acl_user_has_permissions",
        "model_has_roles" => "acl_user_has_roles",
        "role_has_permissions" => "acl_role_has_permissions",
    ],

    "own_resources" => [

        "show",
        "update",
        "destroy",
    ],



    "column_names" => [

        "role_pivot_key" => null,

        "permission_pivot_key" => null,

        "model_morph_key" => "model_id",

        "team_foreign_key" => "team_id",
    ],

    "teams" => false,



    "enable_wildcard_permission" => true,

    "register_permission_check_method" => true,

    "display_permission_in_exception" => false,

    "display_role_in_exception" => false,

    "cache" => [

        "expiration_time" => \DateInterval::createFromDateString("24 hours"),

        "key" => Str::slug(env("APP_NAME"), "_")."_acl",

        "store" => "default",
    ],
];
