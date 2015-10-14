<?php

namespace pszczekutowicz\DiTest\TestAssets;

class ClassWithParam
{
    public $parameter;

    public function __construct($parameter)
    {
        $this->parameter = $parameter;
    }
}
