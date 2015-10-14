<?php

namespace pszczekutowicz\Di;

use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;

class Container implements ContainerInterface
{
    /** @var array */
    protected $definitions;

    /** @var array */
    protected $instances;

    /**
     * @param array $definitions
     */
    public function __construct(array $definitions = null)
    {
        $this->definitions = $definitions;
    }

    /**
     * Check if instance exists or instance definition exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->instances[$name]) || isset($this->definitions[$name]);
    }

    /**
     * Get instance of object.
     *
     * @param $name
     *
     * @return mixed
     */
    public function get($name)
    {
        if (!isset($this->instances[$name])) {
            if (!isset($this->definitions[$name])) {
                throw new RuntimeException(sprintf('Undefined object %s requested', $name));
            }
            $this->instances[$name] = $this->createInstance($name, $this->definitions[$name]);
        }

        return $this->instances[$name];
    }

    /**
     * Create instance from given definition.
     *
     * @param string $name
     * @param mixed  $definition
     *
     * @return mixed
     */
    protected function createInstance($name, $definition)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                sprintf('Name should be string, %s given', gettype($name))
            );
        }

        if (is_callable($definition)) {
            $instance = $this->createInstanceFromCallable($definition);
        } elseif (is_array($definition)) {
            $instance = $this->createInstanceFromDefinition($definition);
        } elseif (is_string($definition)) {
            $instance = ($name !== $definition && $this->has($definition)) ?
                            $this->get($definition) : $this->createInstanceFromClassName($definition);
        } else {
            throw new RuntimeException(
                sprintf('Unknown definition type %s for', gettype($definition), $name)
            );
        }

        return $instance;
    }

    /**
     * Create instance from callback.
     *
     * @param callable $callback
     *
     * @return mixed
     */
    protected function createInstanceFromCallable(callable $callback)
    {
        return $callback();
    }

    /**
     * Create instance from given class name.
     *
     * @param string $className Class name
     *
     * @return mixed
     */
    protected function createInstanceFromClassName($className, array $parameters = null)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException('Class name should be string, %s given', gettype($className));
        }

        if (!class_exists($className)) {
            throw new RuntimeException(sprintf('Class %s does not exists', $className));
        }

        if (interface_exists($className)) {
            throw new RuntimeException(sprintf('Unable to create instance of interface %s', $className));
        }

        switch (count($parameters)) {
            case 0: $instance = new $className();
                break;
            case 1: $instance = new $className($parameters[0]);
                break;
            case 2: $instance = new $className($parameters[0], $parameters[1]);
                break;
            case 3: $instance = new $className($parameters[0], $parameters[1], $parameters[2]);
                break;
            case 4: $instance = new $className($parameters[0], $parameters[1], $parameters[2], $parameters[3]);
                break;
            default:
                $reflection = new ReflectionClass($className);
                $instance = $reflection->newInstanceArgs($parameters);
                break;
        }

        return $instance instanceof FactoryInterface ? $instance->createInstance($this) : $instance;
    }

    /**
     * @param mixed $parameter
     */
    protected function getParameterValue($parameter)
    {
        return is_string($parameter) && $this->has($parameter) ? $this->get($parameter) : $parameter;
    }

    /**
     * Create instance from definition.
     *
     * @param string $name
     * @param array  $definition
     *
     * @return mixed
     */
    protected function createInstanceFromDefinition(array $definition)
    {
        if (!isset($definition['class'])) {
            throw new RuntimeException('Class name missing in instance definition');
        }
        $className = $definition['class'];

        $parameters = null;
        if (isset($definition['parameters'])) {
            $parameters = array();
            foreach ((array) $definition['parameters'] as $value) {
                $parameters[] = $this->getParameterValue($value);
            }
        }

        $instance = $this->createInstanceFromClassName($className, $parameters);

        if (isset($definition['callback'])) {
            foreach ((array) $definition['callback'] as $method => $value) {
                $callback = array($instance, $method);
                if (!is_callable($callback)) {
                    throw new RuntimeException(
                        sprintf('Unable to call %s method on %s', $method, get_class($instance))
                    );
                }
                call_user_func($callback, $this->getParameterValue($value));
            }
        }

        return $instance;
    }
}
