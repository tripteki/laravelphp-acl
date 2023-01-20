<?php

namespace Tripteki\ACL\Models\Admin;

use Spatie\Permission\Models\Permission as Model;
use Tripteki\Uid\Traits\UniqueIdTrait;

class Permission extends Model
{
    use UniqueIdTrait;
};
