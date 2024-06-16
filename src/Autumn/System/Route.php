<?php

namespace Autumn\System;

class Route
{

    public static function matches(Request $request): ?self
    {
        // $method = $request->getMethod();
        // $path = $request->getUri()->getPath();

        return null;
    }

    public function handle(Request $request): Response
    {
        return new Response();
    }
}
