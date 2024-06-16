<?php

namespace Autumn\System\ClassFactory\Tests;

use Autumn\System\ClassFactory\Argument;
use Autumn\System\ClassFactory\Attribute;
use PHPUnit\Framework\TestCase;
use Autumn\System\ClassFactory\Parameter;
use Autumn\System\ClassFactory\Type;
use Autumn\System\ClassFactory\DocComment;

class ParameterTest extends TestCase
{
    public function testConstructor()
    {
        $param = new Parameter('testParam', 'string', true, true, true, 'default', true);

        $this->assertEquals('testParam', $param->getName());
        $this->assertInstanceOf(Type::class, $param->getType());
        $this->assertEquals('string', (string)$param->getType());
        $this->assertTrue($param->isOptional());
        $this->assertTrue($param->isVariadic());
        $this->assertTrue($param->isPassedByReference());
        $this->assertTrue($param->isDefaultValueConstantName());
        $this->assertEquals('default', $param->getDefaultValueConstantName());
    }

    public function testToString()
    {
        $docComment = new DocComment('', ['var'=> 'string']);
        $attribute = new Attribute('TestAttribute', [new Argument('arg', 'value')]);

        $param = new Parameter(
            name: 'paramName',
            type: new Type('string'),
            optional: true,
            variadic: false,
            passedByReference: true,
            defaultValue: 'defaultValue',
            attributes: [$attribute]
        );

        $expected = "#[TestAttribute(arg: 'value')]\r\nstring &\$paramName = 'defaultValue'";
        $this->assertEquals($expected, (string)$param);

        $param = new Parameter(
            name: 'paramName',
            type: 'array',
            optional: true,
            variadic: true,
            passedByReference: false,
            defaultValue: [1, 2, 3],
            attributes: []
        );

        $expected = "array ...\$paramName = array (\n  0 => 1,\n  1 => 2,\n  2 => 3,\n)";
        $this->assertEquals($expected, (string)$param);
    }

    public function testSetName()
    {
        $param = new Parameter('testParam');
        $param->setName('newName');

        $this->assertEquals('newName', $param->getName());

        $this->expectException(\InvalidArgumentException::class);
        $param->setName('invalid-name');
    }

    public function testSetType()
    {
        $param = new Parameter('testParam');
        $param->setType('int');

        $this->assertInstanceOf(Type::class, $param->getType());
        $this->assertEquals('int', (string)$param->getType());
    }

    public function testOptional()
    {
        $param = new Parameter('testParam', null, true);

        $this->assertTrue($param->isOptional());
        $param->setOptional(false);
        $this->assertFalse($param->isOptional());
    }

    public function testVariadic()
    {
        $param = new Parameter('testParam', null, false, true);

        $this->assertTrue($param->isVariadic());
        $param->setVariadic(false);
        $this->assertFalse($param->isVariadic());
    }

    public function testPassedByReference()
    {
        $param = new Parameter('testParam', null, false, false, true);

        $this->assertTrue($param->isPassedByReference());
        $param->setPassedByReference(false);
        $this->assertFalse($param->isPassedByReference());
    }

    public function testDefaultValueConstantName()
    {
        $param = new Parameter('testParam', null, false, false, false, 'CONST_NAME', true);

        $this->assertTrue($param->isDefaultValueConstantName());
        $this->assertEquals('CONST_NAME', $param->getDefaultValueConstantName());

        $param->setDefaultValueConstantName('NEW_CONST_NAME');
        $this->assertEquals('NEW_CONST_NAME', $param->getDefaultValueConstantName());
    }

    public function testGetDefaultValue()
    {
        $param = new Parameter('testParam', null, false, false, false, 'defaultValue');

        $this->assertEquals('defaultValue', $param->getDefaultValue());
    }

    public function testGetComment()
    {
        $param = new Parameter('testParam');
        $this->assertNull($param->getComment());
    }
}
