<?php

use PHPUnit\Framework\TestCase;
use Autumn\System\ClassFactory\Method;
use Autumn\System\ClassFactory\Parameter;
use Autumn\System\ClassFactory\Type;
use Autumn\System\ClassFactory\DocComment;

class MethodTest extends TestCase
{
    public function testConstructAndGetters()
    {
        $method = new Method('methodName');

        $this->assertEquals('methodName', $method->getName());
        $this->assertEquals([], $method->getParameters());
        $this->assertNull($method->getReturnType());
        $this->assertFalse($method->isStatic());
        $this->assertFalse($method->isFinal());
        $this->assertFalse($method->isAbstract());
        $this->assertFalse($method->isPublic());
        $this->assertFalse($method->isProtected());
        $this->assertFalse($method->isPrivate());
        $this->assertFalse($method->isDeprecated());
        $this->assertNull($method->getDocComment());
        $this->assertEquals([], $method->getCodes());
    }

    public function testSetters()
    {
        $method = new Method('methodName');

        $method->setStatic(true);
        $method->setFinal(true);
        $method->setAbstract(true);
        $method->setProtected(true);
        $method->setDeprecated(true);

        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isFinal());
        $this->assertTrue($method->isAbstract());
        $this->assertFalse($method->isPublic());
        $this->assertTrue($method->isProtected());
        $this->assertFalse($method->isPrivate());
        $this->assertTrue($method->isDeprecated());
    }

    public function testAddParameterAndGetParameters()
    {
        $method = new Method('methodName');

        $param1 = new Parameter('param1');
        $param2 = new Parameter('param2', Type::STRING);

        $method->addParameter($param1);
        $method->addParameter($param2);

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertSame($param1, $parameters[0]);
        $this->assertSame($param2, $parameters[1]);
    }

    public function testSetAndGetReturnType()
    {
        $method = new Method('methodName');
        $method->setReturnType(Type::VOID);

        $this->assertInstanceOf(Type::class, $method->getReturnType());
        $this->assertEquals(Type::VOID, (string) $method->getReturnType());
    }

    public function testToString()
    {
        $method = new Method('methodName');
        $method->addParameter(new Parameter('param1', Type::INT));
        $method->setReturnType(Type::STRING);
        $method->append('$result = "Hello World";', 'return $result;');

        $expected = <<<PHP
\tfunction methodName(int \$param1): string
\t{
\t\t\$result = "Hello World";
\t\treturn \$result;
\t}
PHP;

        $this->assertEquals($expected, (string) $method);
    }

    public function testDocComment()
    {
        $method = new Method('methodName');
        $method->addParameter(new Parameter('param1', Type::INT));
        $method->setReturnType(Type::STRING);

        $method->setDocComment('This is a method.');

        $docComment = $method->getDocComment();
        $this->assertInstanceOf(DocComment::class, $docComment);
        $this->assertEquals('/**
 * This is a method.
 * 
 * @param int $param1
 * @return string
 */', (string) $docComment);
    }
}
