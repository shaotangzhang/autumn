<?php

use PHPUnit\Framework\TestCase;
use Autumn\System\ClassFactory\Type;

class TypeTest extends TestCase
{
    public function testTypeFromReflectionNamedType(): void
    {
        $reflectionType = $this->createMock(\ReflectionNamedType::class);
        $reflectionType->method('getName')->willReturn('string');
        $reflectionType->method('allowsNull')->willReturn(false);

        $type = Type::fromReflectionType($reflectionType);

        $this->assertInstanceOf(Type::class, $type);
        $this->assertEquals('string', $type->getName());
        $this->assertFalse($type->allowsNull());
        $this->assertFalse($type->isUnion());
    }

    public function testTypeFromReflectionUnionType(): void
    {
        $reflectionType1 = $this->createMock(\ReflectionNamedType::class);
        $reflectionType1->method('getName')->willReturn('int');
        $reflectionType1->method('allowsNull')->willReturn(false);

        $reflectionType2 = $this->createMock(\ReflectionNamedType::class);
        $reflectionType2->method('getName')->willReturn('float');
        $reflectionType2->method('allowsNull')->willReturn(true);

        $reflectionUnionType = $this->createMock(\ReflectionUnionType::class);
        $reflectionUnionType->method('getTypes')->willReturn([$reflectionType1, $reflectionType2]);
        $reflectionUnionType->method('allowsNull')->willReturn(true);

        $type = Type::fromReflectionType($reflectionUnionType);

        $this->assertInstanceOf(Type::class, $type);
        $this->assertEquals('int|float|null', $type->getName());
        $this->assertTrue($type->allowsNull());
        $this->assertTrue($type->isUnion());
    }

    public function testIntersectionTypes(): void
    {
        $reflectionType1 = $this->createMock(\ReflectionNamedType::class);
        $reflectionType1->method('getName')->willReturn('array');

        $reflectionType2 = $this->createMock(\ReflectionNamedType::class);
        $reflectionType2->method('getName')->willReturn('callable');

        $intersectionTypes = [$reflectionType1, $reflectionType2];

        $intersection = Type::intersectionTypes($intersectionTypes);

        $this->assertCount(2, $intersection);
        $this->assertContainsOnlyInstancesOf(\ReflectionNamedType::class, $intersection);
        $this->assertEquals('array', $intersection[0]->getName());
        $this->assertEquals('callable', $intersection[1]->getName());
    }

    public function testIsScalar(): void
    {
        $typeString = new Type('string');
        $typeArray = new Type('array');

        $this->assertTrue($typeString->isScalar());
        $this->assertFalse($typeArray->isScalar());
    }

    public function testAllowsNull(): void
    {
        $typeNullable = new Type('string|null');
        $typeNotNullable = new Type('int');

        $this->assertTrue($typeNullable->allowsNull());
        $this->assertFalse($typeNotNullable->allowsNull());
    }

    public function testIsUnion(): void
    {
        $typeUnion = new Type('string|int');
        $typeNotUnion = new Type('float');

        $this->assertTrue($typeUnion->isUnion());
        $this->assertFalse($typeNotUnion->isUnion());
    }

    public function testIsValid(): void
    {
        $typeValid = new Type('string');
        $typeInvalid = new Type('unknownType');

        $this->assertTrue($typeValid->isValid());
        $this->assertFalse($typeInvalid->isValid());
    }
}
