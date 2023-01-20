<?php

namespace Tripteki\ACL\Events;

use Illuminate\Queue\SerializesModels as SerializationTrait;

class Granted
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
