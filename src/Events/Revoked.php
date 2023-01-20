<?php

namespace Tripteki\ACL\Events;

use Illuminate\Queue\SerializesModels as SerializationTrait;

class Revoked
{
    use SerializationTrait;

    /**
     * @var mixed
     */
    public $data;

    /**
     * @param mixed $data
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
};
