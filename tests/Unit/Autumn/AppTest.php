<?php
use PHPUnit\Framework\TestCase;
use Autumn\App;
use Composer\Autoload\ClassLoader;

class AppTest extends TestCase
{
    public function testAppSendMethod()
    {
        $appName = 'TestApp';
        $classLoader = new ClassLoader();
        $app = App::boot($appName, $classLoader);

        // 模拟输出捕获
        ob_start();
        $app->send();
        $output = ob_get_clean();

        // 断言输出是否符合预期
        $this->assertEquals('Please modify this class for application `TestApp`!', $output);
    }

    // 如果有其他需要测试的方法，可以继续添加测试方法
}
