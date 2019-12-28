<?php

namespace Joselee214\Ypc\Patch;

class Factory {

    public $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
}
