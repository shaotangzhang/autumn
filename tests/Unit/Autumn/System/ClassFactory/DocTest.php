<?php

use Autumn\System\ClassFactory\Doc;
use Autumn\System\ClassFactory\DocComment;
use PHPUnit\Framework\TestCase;

class DocTest extends TestCase
{
    public function testNormalizeString()
    {
        $input = "Line 1\nLine 2\r\nLine 3\twith tab.";
        $expected = "Line 1" . PHP_EOL . "Line 2" . PHP_EOL . "Line 3    with tab.";
        $this->assertEquals($expected, Doc::normalizeString($input));
    }

    public function testPrint()
    {
        $docCommentMock = $this->createMock(DocComment::class);
        $docCommentMock->expects($this->once())
            ->method('__toString')
            ->willReturn('/** Mock Doc Comment */');

        $output = Doc::print(1, $docCommentMock, 'Line 1', 'Line 2');
        $expected = "/** Mock Doc Comment */" . PHP_EOL . "    Line 1" . PHP_EOL . "    Line 2";
        $this->assertEquals($expected, $output);
    }
    public function testNormalizeClassName()
    {
        // Test with fully qualified class name
        $this->assertEquals('\\Namespace\\ClassName', Doc::normalizeClassName('Namespace\\ClassName'));

        // Test with leading and trailing backslashes
        $this->assertEquals('\\Namespace\\ClassName', Doc::normalizeClassName('\\Namespace\\ClassName\\'));

        // Test with no namespace
        $this->assertEquals('ClassName', Doc::normalizeClassName('ClassName'));

        // Test with empty string
        $this->assertEquals('', Doc::normalizeClassName(''));
    }


    public function testSimplifyClassName()
    {
        // Test with fully qualified class name within namespace
        $this->assertEquals('ClassName', Doc::simplifyClassName('Namespace\\ClassName', 'Namespace'));

        // Test with fully qualified class name but not within namespace
        $this->assertEquals('OtherNamespace\\ClassName', Doc::simplifyClassName('OtherNamespace\\ClassName', 'Namespace'));

        // Test with namespace as the root of the class name
        $this->assertEquals('ClassName', Doc::simplifyClassName('\\Namespace\\ClassName', '\\Namespace'));

        // Test with empty class name
        $this->assertEquals('', Doc::simplifyClassName('', 'Namespace'));

        // Test with empty namespace
        $this->assertEquals('Namespace\\ClassName', Doc::simplifyClassName('Namespace\\ClassName', ''));
    }

    public function testGetClassShortNameWithNamespace()
    {
        $className = 'Autumn\System\ClassFactory\Doc';
        $namespace = '';
        $shortName = Doc::getClassShortName($className, $namespace);
        $expectedShortName = 'Doc';
        $expectedNamespace = 'Autumn\System\ClassFactory';

        $this->assertEquals($expectedShortName, $shortName);
        $this->assertEquals($expectedNamespace, $namespace);
    }

    public function testGetClassShortNameWithoutNamespace()
    {
        $className = 'Doc';
        $namespace = '';
        $shortName = Doc::getClassShortName($className, $namespace);
        $expectedShortName = 'Doc';
        $expectedNamespace = '';

        $this->assertEquals($expectedShortName, $shortName);
        $this->assertEquals($expectedNamespace, $namespace);
    }

    public function testGetClassShortNameEmptyClassName()
    {
        $className = '';
        $namespace = '';
        $shortName = Doc::getClassShortName($className, $namespace);
        $expectedShortName = '';
        $expectedNamespace = '';

        $this->assertEquals($expectedShortName, $shortName);
        $this->assertEquals($expectedNamespace, $namespace);
    }

    public function testGetClassShortNameWithLeadingBackslash()
    {
        $className = '\Doc';
        $namespace = '';
        $shortName = Doc::getClassShortName($className, $namespace);
        $expectedShortName = 'Doc';
        $expectedNamespace = '';

        $this->assertEquals($expectedShortName, $shortName);
        $this->assertEquals($expectedNamespace, $namespace);
    }

    public function testImportableClassNameWithLeadingBackslash()
    {
        $className = '\Autumn\System\ClassFactory\Doc';
        $importableClassName = Doc::importableClassName($className);
        $expectedImportableClassName = 'Autumn\System\ClassFactory\Doc';

        $this->assertEquals($expectedImportableClassName, $importableClassName);
    }

    public function testImportableClassNameWithoutLeadingBackslash()
    {
        $className = 'Autumn\System\ClassFactory\Doc';
        $importableClassName = Doc::importableClassName($className);
        $expectedImportableClassName = 'Autumn\System\ClassFactory\Doc';

        $this->assertEquals($expectedImportableClassName, $importableClassName);
    }

    public function testImportableClassNameEmptyClassName()
    {
        $className = '';
        $importableClassName = Doc::importableClassName($className);
        $expectedImportableClassName = '';

        $this->assertEquals($expectedImportableClassName, $importableClassName);
    }

    public function testNormalizeTextLinesDefaultLineWidth()
    {
        $text = "This is a sample text with\nline breaks that needs normalization.";
        $expected = [
            "This is a sample text with",
            "line breaks that needs normalization."
        ];

        $result = Doc::normalizeTextLines($text);
        $this->assertEquals($expected, $result);
    }

    public function testNormalizeTextLinesCustomLineWidth()
    {
        $text = "This is a longer text that should wrap properly to the specified line width.";
        $expected = [
            "This is a longer text that should wrap",
            "properly to the specified line width."
        ];

        $result = Doc::normalizeTextLines($text, 40); // Use a custom line width
        $this->assertEquals($expected, $result);
    }

    public function testNormalizeTextLinesEmptyText()
    {
        $text = "";
        $expected = [""];

        $result = Doc::normalizeTextLines($text);
        $this->assertEquals($expected, $result);
    }

    public function testNormalizeTextLinesSingleLine()
    {
        $text = "This is a single line text.";
        $expected = ["This is a single line text."];

        $result = Doc::normalizeTextLines($text);
        $this->assertEquals($expected, $result);
    }
}
