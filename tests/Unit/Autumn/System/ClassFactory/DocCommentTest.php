<?php

use PHPUnit\Framework\TestCase;
use Autumn\System\ClassFactory\DocComment;
use Autumn\System\ClassFactory\Annotation;

class DocCommentTest extends TestCase
{
    public function testSetAndGetComment()
    {
        $docComment = new DocComment();
        $comment = "This is a sample class.";
        $docComment->setComment($comment);
        $this->assertEquals($comment, $docComment->getComment());
    }

    public function testAddAnnotation()
    {
        $docComment = new DocComment();
        $docComment->addAnnotation('author', 'John Doe');
        $annotations = $docComment->getAnnotations();
        $this->assertCount(1, $annotations);
        $this->assertEquals('@author John Doe', (string) $annotations[0]);
    }

    public function testAppendAnnotation()
    {
        $docComment = new DocComment();
        $annotation1 = new Annotation('since', 'PHP 8.1');
        $annotation2 = new Annotation('see', 'https://www.php.net/manual/en/language.oop5.php');
        $docComment->appendAnnotation($annotation1, $annotation2);
        $annotations = $docComment->getAnnotations();
        $this->assertCount(2, $annotations);
        $this->assertEquals('@since PHP 8.1', (string) $annotations[0]);
        $this->assertEquals('@see https://www.php.net/manual/en/language.oop5.php', (string) $annotations[1]);
    }

    public function testToString()
    {

        $expectedOutput = <<<EOD
/**
 * This is a sample class.
 * 
 * @author John Doe
 * @version 1.0.0
 */
EOD;

        $docComment = new DocComment();
        $docComment->setComment("This is a sample class.");
        $docComment->addAnnotation('author', 'John Doe');
        $docComment->addAnnotation('version', '1.0.0');
        $this->assertEquals($expectedOutput, (string) $docComment);

        $docComment = new DocComment("This is a sample class.", [
            'author' => 'John Doe',
            'version' => '1.0.0',
        ]);

        $this->assertEquals($expectedOutput, (string) $docComment);
    }

    // public function testBreakLine()
    // {
    //     $docComment = new DocComment();
    //     $docComment->setLineWidth(20);

    //     $lines = $docComment->breakLine("This is a long line that needs to be broken into smaller parts.", 20, " * ");
    //     $expectedLines = [
    //         " * This is a long",
    //         " * line that needs",
    //         " * to be broken into",
    //         " * smaller parts."
    //     ];
    //     $this->assertEquals($expectedLines, $lines);
    // }

    public function testSetAndGetIntent()
    {
        $docComment = new DocComment();
        $intent = "    ";
        $docComment->setIntent($intent);
        $this->assertEquals($intent, $docComment->getIntent());
    }

    public function testSetAndGetLineWidth()
    {
        $docComment = new DocComment();
        $lineWidth = 80;
        $docComment->setLineWidth($lineWidth);
        $this->assertEquals($lineWidth, $docComment->getLineWidth());
    }

    public function testIsDeprecated()
    {
        $docComment = new DocComment('', [new Annotation('deprecated', 'since 1.0')]);
        $this->assertTrue($docComment->isDeprecated());

        $docComment = new DocComment('', ['deprecated' => true]);
        $this->assertTrue($docComment->isDeprecated());

        $docComment = new DocComment('');
        $this->assertFalse($docComment->isDeprecated());
    }

    public function testGetDeprecated()
    {
        $docComment = new DocComment('', [new Annotation('deprecated', 'since 1.0')]);
        $this->assertEquals('since 1.0', $docComment->getDeprecated());

        $docComment = new DocComment;
        $this->assertFalse($docComment->getDeprecated());
    }

    public function testSetDeprecated()
    {
        $docComment = new DocComment;
        $docComment->setDeprecated('since 1.0');
        $this->assertEquals('since 1.0', $docComment->getDeprecated());

        $docComment = new DocComment;
        $docComment->setDeprecated(true);
        $this->assertTrue($docComment->getDeprecated());

        $docComment = new DocComment;
        $docComment->setDeprecated(false);
        $this->assertFalse($docComment->getDeprecated());
    }

    public function testParseBasicComment()
    {
        $comment = <<<COMMENT
/**
 * This is a basic doc comment.
 */
COMMENT;

        $docComment = DocComment::parse($comment);

        $this->assertEquals(DocComment::DEFAULT_LINE_WIDTH, $docComment->getLineWidth());
        $this->assertEquals("This is a basic doc comment.", $docComment->getComment());
        $this->assertEquals('', $docComment->getIntent());
        $this->assertEmpty($docComment->getAnnotations());
        $this->assertEquals($comment, $docComment->__toString());
    }

    public function testParseCommentWithAnnotations()
    {
        $comment = <<<COMMENT
/**
 * @param string \$name The name parameter
 * @param int \$age The age parameter
 * @return bool True if valid, false otherwise
 */
COMMENT;

        $docComment = DocComment::parse($comment);

        $this->assertEquals(80, $docComment->getLineWidth());
        // $this->assertEquals('', $docComment->getComment());
        $this->assertEquals('', $docComment->getIntent());

        $annotations = $docComment->getAnnotations();
        $this->assertCount(3, $annotations);

        $this->assertEquals('param', $annotations[0]->getName());
        $this->assertEquals('string $name The name parameter', $annotations[0]->getContent());

        $this->assertEquals('param', $annotations[1]->getName());
        $this->assertEquals('int $age The age parameter', $annotations[1]->getContent());
    }

    public function testParseCommentWithMultiLineContent()
    {
        $comment = <<<COMMENT
/**
 * This is a multi-line
 * doc comment.
 * @return string The comment content as string
 */
COMMENT;

        $docComment = DocComment::parse($comment);

        $this->assertEquals(80, $docComment->getLineWidth());
        $this->assertEquals("This is a multi-line\ndoc comment.", $docComment->getComment());
        $this->assertEquals('', $docComment->getIntent());

        $annotations = $docComment->getAnnotations();
        $this->assertCount(1, $annotations);

        $this->assertEquals('return', $annotations[0]->getName());
        $this->assertEquals('string The comment content as string', $annotations[0]->getContent());
    }
}
