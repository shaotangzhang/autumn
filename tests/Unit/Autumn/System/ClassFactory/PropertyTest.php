<?php

use PHPUnit\Framework\TestCase;
use Autumn\System\ClassFactory\Property;
use Autumn\System\ClassFactory\Type;
use Autumn\System\ClassFactory\Attribute;

class PropertyTest extends TestCase
{
    public readonly ?string $testProperty;

    public function testFromReflectionProperty()
    {
        $reflectionProperty = new ReflectionProperty(__CLASS__, 'testProperty');

        $property = Property::fromReflectionProperty($reflectionProperty);

        $this->assertEquals('testProperty', $property->getName());
        $this->assertInstanceOf(Type::class, $property->getType());
        $this->assertTrue($property->isReadonly());
        $this->assertTrue($property->isPublic());
        $this->assertFalse($property->isStatic());
        $this->assertFalse($property->isProtected());
        $this->assertFalse($property->isPrivate());
    }

    public function testToString()
    {
        $property = new Property('propertyName', 'string', 'default');
        $property->setStatic(true);
        $property->setReadonly(true);
        $property->setProtected(true);
        // $property->getDocComment(true)->setComment('This is a property');
        // $property->setDeprecated('since 1.0');
        // $property->addAttribute(new Attribute('Symfony\Component\Serializer\Annotation\Groups', ['foo', 'bar']));

        $expectedString = "\tprotected static readonly string \$propertyName = 'default';";
        $actualString = (string) $property;

        $this->assertEquals($expectedString, $actualString);
    }
}
