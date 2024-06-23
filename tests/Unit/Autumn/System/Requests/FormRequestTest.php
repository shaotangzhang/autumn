<?php
namespace Autumn\System\Requests;

use PHPUnit\Framework\TestCase;

class FormRequestTest extends TestCase
{
    public function testParseRule()
    {
        // Test case 1: Basic rule without arguments
        $rule1 = 'required';
        $expected1 = [
            'required' => [],
        ];
        $this->assertEquals($expected1, (FormRequest::parseRule($rule1)));

        // Test case 2: Rule with arguments
        $rule2 = 'time:Y-m-d\,H\:i\:s';
        $expected2 = [
            'time' => ['Y-m-d,H:i:s'],
        ];
        $this->assertEquals($expected2, (FormRequest::parseRule($rule2)));

        // Test case 3: Rule with escaped characters in name and arguments
        $rule3 = 'required|date:Y-m-d\,H\:i\:s';
        $expected3 = [
            'required' => [],
            'date' => ['Y-m-d,H:i:s'],
        ];
        $this->assertEquals($expected3, (FormRequest::parseRule($rule3)));

        // Test case 4: Rule with multiple arguments
        $rule4 = 'between:5,10';
        $expected4 = [
            'between' => ['5', '10'],
        ];
        $this->assertEquals($expected4, (FormRequest::parseRule($rule4)));

        // Test case 5: Rule with boolean arguments
        $rule5 = 'boolean:true,false';
        $expected5 = [
            'boolean' => ['true', 'false'],
        ];
        $this->assertEquals($expected5, (FormRequest::parseRule($rule5)));

        // Test case 6: Rule with escaped characters in arguments
        $rule6 = 'in:foo\,bar,baz\:qux';
        $expected6 = [
            'in' => ['foo,bar', 'baz:qux'],
        ];
        $this->assertEquals($expected6, (FormRequest::parseRule($rule6)));
    }
}
