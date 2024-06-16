<?php

use App;

class Application extends \Autumn\System\Application
{
    public static function main(string ...$args): void
    {
        exit('Hello world!');
    }

    public function exceptionHandler(\Throwable $exception): void
    {
        exit('Application error: ' . $exception->getMessage());
    }
}
