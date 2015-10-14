<?php

namespace pszczekutowicz\DiTest\TestAssets;

use pszczekutowicz\Di\ContainerInterface;
use pszczekutowicz\Di\FactoryInterface;
use StdClass as TestClass;

class TestClassFactory implements FactoryInterface
{
    public function createInstance(ContainerInterface $container)
    {
        return new TestClass();
    }
}
