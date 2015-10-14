<?php

namespace pszczekutowicz\DiTest;

use pszczekutowicz\Di\Container;
use PHPUnit_Framework_TestCase as TestCase;
use StdClass as TestClass;

class ContainerTest extends TestCase
{
    public function simpleContainerDefinitionsDataProvider()
    {
        return [
            [
                ['key' => TestClass::class],
            ],
        ];
    }

    /**
     * @dataProvider simpleContainerDefinitionsDataProvider
     */
    public function testContainerHasMethod(array $definitions)
    {
        $container = new Container($definitions);

        foreach ($definitions as $name => $className) {
            $this->assertTrue($container->has($name));
        }

        $this->assertFalse($container->has('unknown'));
    }

    /**
     * @dataProvider simpleContainerDefinitionsDataProvider
     */
    public function testGetInstance(array $definitions = null)
    {
        $container = new Container($definitions);

        foreach ($definitions as $key => $className) {
            $this->assertInstanceOf($className, $container->get($key));
        }
    }

    /**
     * @dataProvider simpleContainerDefinitionsDataProvider
     * @expectedException \RuntimeException
     */
    public function testExceptionOnUndefinedInstance(array $definitions)
    {
        $container = new Container($definitions);

        $container->get('instance');
    }

    public function testGetInstanceFromFactory()
    {
        $container = new Container(array('instance' => TestAssets\TestClassFactory::class));

        $this->assertInstanceOf(TestClass::class, $container->get('instance'));
    }

    public function testGetInstanceFromDefinition()
    {
        $definitions = [
            'key' => [
                'class' => TestClass::class,
            ],
        ];
        $container = new Container($definitions);

        $this->assertInstanceOf(TestClass::class, $container->get('key'));
    }

    public function testGetInstanceFromDefinitionWithFactory()
    {
        $definitions = [
            'key' => [
                'class' => TestAssets\TestClassFactory::class,
            ],
        ];
        $container = new Container($definitions);

        $this->assertInstanceOf(TestClass::class, $container->get('key'));
    }

    public function testGetInstanceFromDefinitionWithFactoryAndDependency()
    {
        $definitions = [
            'key1' => TestClass::class,
            'key2' => [
                'class' => TestAssets\FactoryWithDependency::class,
            ],
        ];
        $container = new Container($definitions);

        $instance = $container->get('key2');
        $this->assertInstanceOf(TestAssets\ClassWithParam::class, $instance);
        $this->assertInstanceOf(TestClass::class, $instance->parameter);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetInstanceFromDefinitionWithFactoryAndMissingDependency()
    {
        $definitions = [
            'key' => [
                'class' => TestAssets\FactoryWithDependency::class,
            ],
        ];
        $container = new Container($definitions);

        $container->get('key');
    }

    public function testGetInstanceWithDependencies()
    {
        $definitions = [
            'key1' => TestClass::class,
            'key2' => [
                'class' => TestAssets\ClassWithParam::class,
                'parameters' => ['key1'],
            ],
            'key3' => [
                'class' => TestAssets\ClassWithParam::class,
                'parameters' => ['key2'],
            ],

        ];
        $container = new Container($definitions);
        $instance = $container->get('key3');

        $this->assertInstanceOf(TestAssets\ClassWithParam::class, $instance);
        $this->assertInstanceOf(TestAssets\ClassWithParam::class, $instance->parameter);
        $this->assertInstanceOf(TestClass::class, $instance->parameter->parameter);
    }

    public function testGetInstanceWithCallbackAndDependencies()
    {
        $definitions = [
            'key1' => TestClass::class,
            'key2' => [
                'class' => TestAssets\ClassWithSetter::class,
                'callback' => [
                    'setParameter' => 'key1',
                ],
            ],
            'key3' => [
                'class' => TestAssets\ClassWithSetter::class,
                'callback' => [
                    'setParameter' => 'key2',
                ],
            ],
        ];
        $container = new Container($definitions);
        $instance = $container->get('key3');

        $this->assertInstanceOf(TestAssets\ClassWithSetter::class, $instance);
        $this->assertInstanceOf(TestAssets\ClassWithSetter::class, $instance->parameter);
        $this->assertInstanceOf(TestClass::class, $instance->parameter->parameter);
    }

    public function testGetInstaceWithCallable()
    {
        $definitions = [
            'key1' => function () {
                return new TestClass();
            },
        ];

        $container = new Container($definitions);
        $this->assertInstanceOf(TestClass::class, $container->get('key1'));
    }
}
