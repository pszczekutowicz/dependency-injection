<?php

namespace pszczekutowicz\DiTest\TestAssets;

class ClassWithSetter
{
    public $parameter;

    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
    }
}
