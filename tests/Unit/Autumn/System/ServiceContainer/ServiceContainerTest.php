<?php

use PHPUnit\Framework\TestCase;
use Autumn\System\ServiceContainer\ServiceContainer;
use Autumn\Interfaces\SingletonInterface;

class ServiceContainerTest extends TestCase
{
    private ServiceContainer $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new ServiceContainer();
    }

    public function testBindObjectInstance(): void
    {
        $instance = new stdClass();
        $this->container->bind('stdClass', $instance);
        $resolvedInstance = $this->container->make('stdClass');
        $this->assertSame($instance, $resolvedInstance);
    }

    public function testBindClass(): void
    {
        $this->container->bind('SomeClass', SomeClass::class);
        $resolvedInstance = $this->container->make('SomeClass');
        $this->assertInstanceOf(SomeClass::class, $resolvedInstance);
    }

    public function testBindCallable(): void
    {
        $this->container->bind('someFunction', fn () => 'Hello, world!');
        $result = $this->container->make('someFunction');
        $this->assertEquals('Hello, world!', $result);
    }

    public function testBindInvalidBinding(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->container->bind('invalid', 12345);
    }

    public function testMakeSingleton(): void
    {
        $this->container->bind('SingletonClass', SingletonClass::class);
        $instance1 = $this->container->make('SingletonClass');
        $instance2 = $this->container->make('SingletonClass');
        $this->assertSame($instance1, $instance2);
    }

    public function testMakeNotBound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->container->make('NotBoundClass');
    }

    // Add more tests as needed for other scenarios and edge cases

}

// Example classes used in tests

class SomeClass {}

class SingletonClass implements SingletonInterface {
    private static ?self $instance = null;

    public static function getInstance(): static {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}
}
