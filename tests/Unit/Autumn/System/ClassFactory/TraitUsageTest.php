<?php

namespace Autumn\System\ClassFactory\Tests;

use PHPUnit\Framework\TestCase;
use Autumn\System\ClassFactory\TraitUsage;

class TraitUsageTest extends TestCase
{
    public function testToStringWithoutModifications()
    {
        $traitUsage = new TraitUsage('TraitOne', 'TraitTwo');
        $expected = "\tuse TraitOne, TraitTwo;";
        $this->assertEquals($expected, (string)$traitUsage);
    }

    public function testToStringWithModifications()
    {
        $traitUsage = new TraitUsage('TraitOne', 'TraitTwo');
        $traitUsage->modify('methodOne', 'public', 'methodAlias');
        $traitUsage->select('TraitTwo', 'methodTwo', 'TraitOne');

        $expected = <<<EOD
\tuse TraitOne, TraitTwo{
\t\tmethodOne as public methodAlias;
\t\tTraitTwo::methodTwo insteadof TraitOne;
\t}
EOD;

        $this->assertEquals($expected, (string)$traitUsage);
    }

    public function testModifyMethod()
    {
        $traitUsage = new TraitUsage('TraitOne');
        $traitUsage->modify('methodOne', 'public', 'aliasMethodOne');
        
        $modifications = $traitUsage->getModifiers();
        $this->assertArrayHasKey('methodOne', $modifications);
        $this->assertEquals(['methodOne', 'as', 'public aliasMethodOne'], $modifications['methodOne']);
    }

    public function testSelectMethod()
    {
        $traitUsage = new TraitUsage('TraitOne', 'TraitTwo');
        $traitUsage->select('TraitOne', 'method', 'TraitTwo');
        
        $modifications = $traitUsage->getModifiers();
        $this->assertArrayHasKey('TraitOne::method', $modifications);
        $this->assertEquals(['TraitOne::method', 'insteadof', 'TraitTwo'], $modifications['TraitOne::method']);
    }
}
