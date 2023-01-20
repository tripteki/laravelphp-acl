<?php

namespace Tripteki\ACL\Models\Admin;

use Spatie\Permission\Models\Role as Model;
use Tripteki\Uid\Traits\UniqueIdTrait;

class Role extends Model
{
    use UniqueIdTrait;
};
