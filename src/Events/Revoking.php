<?php

namespace Tripteki\ACL\Events;

use Illuminate\Queue\SerializesModels as SerializationTrait;

class Revoking
{
    use SerializationTrait;

    /**
     * @var \Illuminate\Http\Request
     */
    public $data;

    /**
     * @param \Illuminate\Http\Request $data
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
};
