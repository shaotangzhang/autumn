<?php

namespace Autumn\System\ClassFactory\Tests;

use PHPUnit\Framework\TestCase;
use Autumn\System\ClassFactory\Argument;
use Autumn\System\ClassFactory\Attribute;

class AttributeTest extends TestCase
{
    public function testAttributeToStringWithArguments()
    {
        $arguments = [
            'param1' => 42,
            new Argument('param2', 'value'),
            new Argument(null, [1, 2, 3], true),
        ];
        $attribute = new Attribute('TestAttribute', $arguments);
        $expected = '#[TestAttribute(param1: 42, param2: \'value\', ...array(0=>1,1=>2,2=>3,))]';

        // Normalize the expected output to account for whitespace differences
        $expected = preg_replace('/\s+/', '', $expected);

        $this->assertEquals($expected, preg_replace('/\s+/', '', (string) $attribute));
    }

    public function testAttributeToStringWithoutArguments()
    {
        $attribute = new Attribute('EmptyAttribute');
        $expected = '#[EmptyAttribute]';

        $this->assertEquals($expected, (string) $attribute);
    }

    public function testGetName()
    {
        $attribute = new Attribute('TestAttribute');
        $this->assertEquals('TestAttribute', $attribute->getName());
    }

    public function testGetArguments()
    {
        $arguments = [
            'param1' => 42,
            new Argument('param2', 'value'),
        ];
        $attribute = new Attribute('TestAttribute', $arguments);
        $expected = [
            new Argument('param1', 42),
            new Argument('param2', 'value'),
        ];

        $this->assertEquals($expected, $attribute->getArguments());
    }

    public function testAddArgument()
    {
        $attribute = new Attribute('TestAttribute');
        $argument = new Argument('param1', 42);
        $attribute->addArgument($argument);

        $this->assertEquals([$argument], $attribute->getArguments());
    }

    public function testAddArg()
    {
        $attribute = new Attribute('TestAttribute');
        $attribute->addArg('param1', 42);

        $arguments = $attribute->getArguments();
        $this->assertCount(1, $arguments);
        $this->assertEquals('param1', $arguments[0]->getName());
        $this->assertEquals(42, $arguments[0]->getValue());
    }
}
