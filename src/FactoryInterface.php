<?php

namespace pszczekutowicz\Di;

interface FactoryInterface
{
    /**
     * Create instance of object.
     *
     * @param ContainerInterface $container
     *
     * @return mixed
     */
    public function createInstance(ContainerInterface $container);
}
