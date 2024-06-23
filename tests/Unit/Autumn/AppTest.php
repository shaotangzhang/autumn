<?php

namespace Tests\Unit\Autumn;

use PHPUnit\Framework\TestCase;
use Autumn\App;
use Autumn\System\Application;
use Autumn\System\Request;
use Autumn\System\Response;
use Composer\Autoload\ClassLoader;
use Psr\Http\Message\ResponseInterface;

class AppTest extends TestCase
{
    public function testBoot()
    {
        $classLoader = new ClassLoader();

        // Mock DOC_ROOT for testing purposes
        define('DOC_ROOT', __DIR__ . '/../../../');

        // Mock Application class for testing purposes
        $appClass = new class extends Application
        {
            public function handle(Request $request): ResponseInterface
            {
                return new Response('Hello, World!');
            }
        };

        // Call the boot method
        $response = App::boot($appClass::class, $classLoader);

        // Assert the response content
        $this->assertEquals('Hello, World!', $response->getContent());
    }
}
