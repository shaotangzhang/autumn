<?php

use PHPUnit\Framework\TestCase;
use Autumn\System\ClassFactory\Annotation;

class AnnotationTest extends TestCase
{
    public function testAnnotationInitialization()
    {
        $annotation = new Annotation('author', 'John Doe');
        $this->assertEquals('author', $annotation->getName());
        $this->assertEquals('John Doe', $annotation->getContent());
    }

    public function testGetName()
    {
        $annotation = new Annotation('author', 'John Doe');
        $this->assertEquals('author', $annotation->getName());
    }

    public function testSetName()
    {
        $annotation = new Annotation('author', 'John Doe');
        $annotation->setName('version');
        $this->assertEquals('version', $annotation->getName());
    }

    public function testGetContent()
    {
        $annotation = new Annotation('author', 'John Doe');
        $this->assertEquals('John Doe', $annotation->getContent());
    }

    public function testSetContent()
    {
        $annotation = new Annotation('author', 'John Doe');
        $annotation->setContent('1.0.0');
        $this->assertEquals('1.0.0', $annotation->getContent());
    }

    public function testToString()
    {
        $annotation = new Annotation('author', 'John Doe');
        $this->assertEquals('@author John Doe', (string) $annotation);
    }
}
