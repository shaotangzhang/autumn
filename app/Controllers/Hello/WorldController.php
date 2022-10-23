<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/20
 */

namespace App\Controllers\Hello;

use Autumn\System\Attributes\ResponseEntity;
use Autumn\System\Controller;

class WorldController extends Controller
{
    #[ResponseEntity(timestamp: false)]
    public function index(string $name): array
    {
        // GET https://xxxx/hello/world?name=abc

        return [
            'name' => $name,
            'text' => 'Hello world!'
        ];
    }
}