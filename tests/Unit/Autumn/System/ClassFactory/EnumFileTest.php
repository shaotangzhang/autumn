<?php

use PHPUnit\Framework\TestCase;
use Autumn\System\ClassFactory\EnumFile;

class EnumFileTest extends TestCase
{
    public function testEnumFileCreation()
    {
        $enumFile = new EnumFile('Status', true);

        $this->assertEquals('Status', $enumFile->getClassName());
        $this->assertTrue($enumFile->isBackedType());
    }

    public function testSetBackedType()
    {
        $enumFile = new EnumFile('Status');
        $this->assertFalse($enumFile->isBackedType());

        $enumFile->setBackedType(true);
        $this->assertTrue($enumFile->isBackedType());
    }

    public function testCaseMethod()
    {
        $enumFile = new EnumFile('Status', true);
        $enumFile->case('ACTIVE', 1);
        $enumFile->case('INACTIVE', 0);

        $cases = $enumFile->getCases();

        $this->assertCount(2, $cases);
        $this->assertEquals(1, $cases['ACTIVE']);
        $this->assertEquals(0, $cases['INACTIVE']);
    }

    public function testCaseWithoutValueForBackedEnum()
    {
        $this->expectException(\InvalidArgumentException::class);

        $enumFile = new EnumFile('Status', true);
        $enumFile->case('ACTIVE');
    }

    public function testToString()
    {
        $enumFile = new EnumFile('Status', true);
        $enumFile->setNamespace('App\\Enums');
        $enumFile->case('ACTIVE', 1);
        $enumFile->case('INACTIVE', 0);

        $expected = <<<EOT
namespace App\Enums;

enum Status extends \BackedEnum
{
\tcase ACTIVE = 1;
\tcase INACTIVE = 0;
}
EOT;

        $this->assertEquals($expected, (string)$enumFile);
    }
}
