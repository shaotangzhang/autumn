<?php

namespace Autumn\System\ClassFactory\Tests;

use PHPUnit\Framework\TestCase;
use Autumn\System\ClassFactory\Constant;
use Autumn\System\ClassFactory\Type;
use Autumn\System\ClassFactory\DocComment;

class ConstantTest extends TestCase
{
    public function testToString()
    {
        $docComment = new DocComment('', ['var' => 'string']);

        $constant = new Constant(
            name: 'CONST_NAME',
            value: 'someValue',
            comment: $docComment
        );
        $constant->setPublic(true);

        $expected = "\t/**\r\n\t * @var string\r\n\t */\r\n\tpublic const CONST_NAME = 'someValue';";
        $this->assertEquals($expected, (string)$constant);

        $constant = new Constant(
            name: 'CONST_NAME',
            value: 42,
            comment: 'A constant'
        );
        $constant->setPrivate(true);

        $expected = "\t/**\r\n\t * A constant\r\n\t */\r\n\tprivate const CONST_NAME = 42;";
        $this->assertEquals($expected, (string)$constant);

        $constant = new Constant(
            name: 'CONST_NAME',
            value: [1, 2, 3],
            comment: ''
        );
        $constant->setProtected(true);

        $expected = "\t/**\r\n\t */\r\n\tprotected const CONST_NAME = array (\n  0 => 1,\n  1 => 2,\n  2 => 3,\n);";
        $this->assertEquals($expected, (string)$constant);
    }

    public function testModifiers()
    {
        $constant = new Constant('CONST_NAME', 'value');

        $constant->setPublic(true);
        $this->assertTrue($constant->isPublic());
        $this->assertFalse($constant->isProtected());
        $this->assertFalse($constant->isPrivate());

        $constant->setProtected(true);
        $this->assertFalse($constant->isPublic());
        $this->assertTrue($constant->isProtected());
        $this->assertFalse($constant->isPrivate());

        $constant->setPrivate(true);
        $this->assertFalse($constant->isPublic());
        $this->assertFalse($constant->isProtected());
        $this->assertTrue($constant->isPrivate());

        $constant->setFinal(true);
        $this->assertTrue($constant->isFinal());

        $constant->setFinal(false);
        $this->assertFalse($constant->isFinal());
    }

    public function testDeprecated()
    {
        $constant = new Constant('CONST_NAME', 'value',  false, ['deprecated' => true]);
        $this->assertTrue($constant->isDeprecated());

        $constant->setDeprecated(false);
        $this->assertFalse($constant->isDeprecated());

        $constant->setDeprecated('1.1');
        $this->assertTrue($constant->isDeprecated());
    }

    public function testGetAndSetValue()
    {
        $constant = new Constant('CONST_NAME', 'value');
        $this->assertEquals('value', $constant->getValue());

        $constant->setValue(42);
        $this->assertEquals(42, $constant->getValue());

        $constant->setValueConstantName('NEW_CONST');
        $constant->setPublic(true);
        $this->assertEquals('NEW_CONST', $constant->getValue());
        $this->assertEquals("\tpublic const CONST_NAME = NEW_CONST;", (string)$constant);
    }
}
