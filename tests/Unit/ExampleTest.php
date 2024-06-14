<?php

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testExampleMethod()
    {
        $example = "Hello world!";

        // 断言期望结果与实际结果相符
        $this->assertEquals('Hello world!', $example);
    }
}
