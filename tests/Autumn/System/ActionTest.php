<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/5
 */

namespace Autumn\System;

use Autumn\App;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ActionTest extends TestCase
{

    public function testParse()
    {
        Assert::assertEquals(
            ['class' => 'App\\Controllers\\Study\\Pte\\ReadingController', 'name' => 'RA/recent', 'id' => '123'],
            Action::parse('/study/pte/reading/RA/recent/123')
        );
        Assert::assertEquals(
            ['class' => 'App\\Controllers\\Study\\Pte\\ReadingController', 'name' => null, 'id' => '123'],
            Action::parse('/study/pte/reading/123')
        );
        Assert::assertEquals(
            ['class' => 'App\\Controllers\\Study\\Pte\\ReadingController', 'name' => 'RA/recent'],
            Action::parse('/study/pte/reading/RA/recent')
        );
        Assert::assertEquals(
            ['class' => 'App\\Controllers\\Study\\Pte\\ReadingController'],
            Action::parse('/study/pte/reading')
        );

        Assert::assertEquals(
            ['class' => 'App\\Controllers\\IndexController', 'name' => 'RA/recent', 'id' => '123'],
            Action::parse('/RA/recent/123')
        );
        Assert::assertEquals(
            ['class' => 'App\\Controllers\\IndexController', 'name' => null, 'id' => '123'],
            Action::parse('/123')
        );
        Assert::assertEquals(
            ['class' => 'App\\Controllers\\IndexController', 'name' => 'RA/recent'],
            Action::parse('/RA/recent')
        );
        Assert::assertEquals(
            ['class' => 'App\\Controllers\\IndexController'],
            Action::parse('')
        );
        Assert::assertEquals(
            ['class' => 'App\\Controllers\\IndexController'],
            Action::parse('/')
        );
    }

    public function testResolve()
    {
        $_SERVER['PATH_INFO'] = '/RA/K123';

        App::run()->end();
    }
}
