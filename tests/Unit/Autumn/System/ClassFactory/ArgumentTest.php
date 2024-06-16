<?php

namespace Autumn\System\ClassFactory\Tests;

use PHPUnit\Framework\TestCase;
use Autumn\System\ClassFactory\Argument;

class ArgumentTest extends TestCase
{
    public function testArgumentToStringWithName()
    {
        $argument = new Argument('param1', 42);
        $expected = 'param1: 42';
        
        $this->assertEquals($expected, (string) $argument);

        $argument = new Argument('version', 'self::PHP_MIN_VERSION_ID', false, true);
        $expected = 'version: self::PHP_MIN_VERSION_ID';
        $this->assertEquals($expected, (string) $argument);
    }

    public function testArgumentToStringWithoutName()
    {
        $argument = new Argument(null, [1, 2, 3], true);
        $expected = '...array (
            0 => 1,
            1 => 2,
            2 => 3,
        )';

        // Normalize the expected output to account for whitespace differences
        $expected = preg_replace('/\s+/', '', $expected);

        $this->assertEquals($expected, preg_replace('/\s+/', '', (string) $argument));
    }

    public function testGetName()
    {
        $argument = new Argument('param1', 42);
        $this->assertEquals('param1', $argument->getName());
    }

    public function testGetValue()
    {
        $argument = new Argument('param1', 42);
        $this->assertEquals(42, $argument->getValue());
    }

    public function testIsVariadic()
    {
        $argument1 = new Argument('param1', 42);
        $this->assertFalse($argument1->isVariadic());

        $argument2 = new Argument(null, [1, 2, 3], true);
        $this->assertTrue($argument2->isVariadic());
    }
}
