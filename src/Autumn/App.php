<?php

namespace Autumn;

use Composer\Autoload\ClassLoader;

final class App 
{
    private static ClassLoader $classLoader;

    private function __construct(private readonly string $appName)
    {

    }

    public static function boot(string $appName, ClassLoader $classLoader)
    {
        self::$classLoader = $classLoader;

        return new self($appName);
    }

    public function send(): void
    {
        echo sprintf('Please modify this class for application `%s`!', $this->appName);
    }
}