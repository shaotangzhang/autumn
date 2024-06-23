<?php
/**
 * Autumn PHP Framework
 *
 * Date:        16/06/2024
 */

namespace Autumn\System;

use Autumn\App;
use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;

class ExtensionTest extends TestCase
{
//    public static function setUpBeforeClass(): void
//    {
//        App::boot('app', new ClassLoader);
//    }

    public function testVerifyExtensionRequirementsThrowsExceptionOnInvalidExtension()
    {
        $this->expectException(\RuntimeException::class);

        MockExtension::context();
    }

}

class MockExtension extends Extension
{
    public const REQUIRED_EXTENSIONS = [
        'InvalidExtension' => '1.0.0'
    ];
}