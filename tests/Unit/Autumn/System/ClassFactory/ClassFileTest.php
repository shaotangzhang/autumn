<?php

use PHPUnit\Framework\TestCase;
use Autumn\System\ClassFactory\ClassFile;
use Autumn\System\ClassFactory\Method;
use Autumn\System\ClassFactory\Property;

class ClassFileTest extends TestCase
{
    public function testToStringWithNamespaceAndImports()
    {
        $classFile = new ClassFile('\Autumn\System\ClassFactory\MyClass');
        $classFile->import('DateTime');
        $classFile->import('Exception', 'Ex');

        $expected = <<<PHP
namespace Autumn\System\ClassFactory;

use DateTime;
use Exception as Ex;

class MyClass
{
}
PHP;

        $this->assertEquals($expected, (string) $classFile);
    }

    public function testToStringWithDocComment()
    {
        $classFile = new ClassFile('MyClass');
        $classFile->setDocComment('This is a class.');

        $expected = <<<PHP
/**
 * This is a class.
 */
class MyClass
{
}
PHP;

        $this->assertEquals($expected, (string) $classFile);
    }

    public function testToStringWithExtendsAndImplements()
    {
        $classFile = new ClassFile('MyClass', 'ParentClass', 'Interface1', 'Interface2');

        $expected = <<<PHP
class MyClass extends ParentClass
\timplements Interface1, Interface2
{
}
PHP;

        $this->assertEquals($expected, (string) $classFile);
    }

    public function testToStringWithTraits()
    {
        $classFile = new ClassFile('MyClass');
        $classFile->use('Trait1');
        $classFile->use('Trait2', 'TraitAlias');

        $expected = <<<PHP
class MyClass
{
\tuse Trait1;
\tuse Trait2, TraitAlias;
}
PHP;

        $this->assertEquals($expected, (string) $classFile);
    }

    public function testToStringWithConstants()
    {
        $classFile = new ClassFile('MyClass');
        $classFile->addConstant('CONST1', 'value1');
        $classFile->addConstant('CONST2', 'value2');

        $expected = <<<PHP
class MyClass
{
\tconst CONST1 = 'value1';
\tconst CONST2 = 'value2';
}
PHP;

        $this->assertEquals($expected, (string) $classFile);
    }

    public function testToStringWithProperties()
    {
        $classFile = new ClassFile('MyClass');
        $classFile->addProperty(new Property('publicProperty', 'public'));
        $classFile->addProperty(new Property('protectedProperty', 'protected'));
        $classFile->addProperty(new Property('privateProperty', 'private'));

        $expected = <<<PHP
class MyClass
{
\tpublic \$publicProperty;
\tprotected \$protectedProperty;
\tprivate \$privateProperty;
}
PHP;

        $this->assertEquals($expected, (string) $classFile);
    }

    public function testToStringWithMethods()
    {
        $classFile = new ClassFile('MyClass');
        $method1 = new Method('publicMethod');
        $method1->setPublic(true);
        $method1->append('return true;');
        $classFile->addMethod($method1);

        $method2 = new Method('protectedMethod');
        $method2->setProtected(true);
        $method2->append('return false;');
        $classFile->addMethod($method2);

        $expected = <<<PHP
class MyClass
{
\tpublic function publicMethod()
\t{
\t\treturn true;
\t}

\tprotected function protectedMethod()
\t{
\t\treturn false;
\t}
}
PHP;

        $this->assertEquals($expected, (string) $classFile);
    }
}
