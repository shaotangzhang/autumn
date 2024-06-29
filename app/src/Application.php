<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace App;

use App\Providers\AppServiceProvider;
use Autumn\Database\DatabaseServiceProvider;
use Autumn\System\Response;
use Autumn\System\Responses\ResponseService;
use Autumn\System\Session;

class Application extends \Autumn\System\Application
{
    protected array $serviceProviders = [
        DatabaseServiceProvider::class,   // This can be a must if ORM is used
        AppServiceProvider::class,
    ];

    public function exceptionHandler(\Throwable $exception): void
     {
         $response = ResponseService::context()->respond($exception);
         Response::fromResponseInterface($response)->send();
         exit;
     }
}