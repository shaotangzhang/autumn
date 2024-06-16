<?php

use Autumn\System\ClassFactory\interfaceFile;
use Autumn\System\ClassFactory\Method;
use PHPUnit\Framework\TestCase;

class InterfaceFileTest extends TestCase
{
    public function testInterfaceFileCreation()
    {
        $interface = new interfaceFile('MyInterface');

        $this->assertEquals('MyInterface', $interface->getClassName());
        $this->assertEmpty($interface->getNamespace());
        $this->assertEmpty($interface->getExtends());
        $this->assertEmpty($interface->getMethods());
        $this->assertEmpty($interface->getConstants());
        $this->assertEmpty($interface->getImports());
    }

    public function testNamespaceSetting()
    {
        $interface = new interfaceFile('MyInterface');
        $interface->setNamespace('My\\Namespace');

        $this->assertEquals('My\\Namespace', $interface->getNamespace());
    }

    public function testExtendsSetting()
    {
        $interface = new interfaceFile('MyInterface', 'BaseInterface');
        
        $this->assertEquals(['\\BaseInterface'], $interface->getExtends());

        $interface->extends('AnotherInterface');
        $this->assertEquals(['\\BaseInterface', '\\AnotherInterface'], $interface->getExtends());
    }

    public function testMethodAddition()
    {
        $interface = new interfaceFile('MyInterface');
        $method = new Method('myMethod');
        $interface->addMethod($method);

        $this->assertCount(1, $interface->getMethods());
        $this->assertEquals($method, $interface->getMethods()['myMethod']);
    }

    public function testConstantAddition()
    {
        $interface = new interfaceFile('MyInterface');
        $interface->addConstant('MY_CONSTANT', 42);

        $this->assertCount(1, $interface->getConstants());
        $constant = $interface->getConstants()['MY_CONSTANT'];
        $this->assertEquals('MY_CONSTANT', $constant->getName());
        $this->assertEquals(42, $constant->getValue());
    }

    public function testImportSetting()
    {
        $interface = new interfaceFile('MyInterface');
        $interface->import('My\\ImportedClass');

        $this->assertCount(1, $interface->getImports());
        $this->assertEquals('\\My\\ImportedClass', $interface->getImports()['ImportedClass']);
    }

    public function testDocCommentSetting()
    {
        $interface = new interfaceFile('MyInterface');
        $interface->setDocComment('This is a doc comment.');

        $this->assertNotNull($interface->getDocComment());
        $this->assertEquals('This is a doc comment.', $interface->getDocComment()->getComment());
    }

    public function testToString()
    {
        $interface = new InterfaceFile('MyInterface', '\BaseInterface');
        $interface->setNamespace('My\\Namespace');
        $interface->import('My\\ImportedClass');
        $interface->addConstant('MY_CONSTANT', 42);
        $method = new Method('myMethod');
        $interface->addMethod($method);

        $expected = <<<EOD
namespace My\Namespace;

use My\ImportedClass;

interface MyInterface extends \BaseInterface
{
\tconst MY_CONSTANT = 42;

\tfunction myMethod();
}
EOD;

        $this->assertEquals($expected, (string) $interface);
    }
}
