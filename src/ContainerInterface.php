<?php

namespace pszczekutowicz\Di;

interface ContainerInterface
{
    /**
     * Check if instance exists or instance definition exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * Get instance of object.
     *
     * @param $name
     *
     * @return mixed
     */
    public function get($name);
}
