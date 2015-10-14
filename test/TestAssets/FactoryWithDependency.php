<?php

namespace pszczekutowicz\DiTest\TestAssets;

use pszczekutowicz\Di\ContainerInterface;
use pszczekutowicz\Di\FactoryInterface;

class FactoryWithDependency implements FactoryInterface
{
    public function createInstance(ContainerInterface $container)
    {
        return new ClassWithParam($container->get('key1'));
    }
}
