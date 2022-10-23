<?php
declare(strict_types=1);

use App\Services\Security\SecurityService;
use Autumn\Security\Security;
use Autumn\Security\Session\SecuritySessionContext;

return [
    'interfaces' => [
    ],

    'plugins' => [
//        Security::class => [
//            'context' => SecuritySessionContext::class,
//            'configure' => SecurityService::class,
//        ]
    ],

    'filters' => [
    ],
];