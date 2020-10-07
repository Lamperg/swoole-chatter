<?php

namespace App;

use FastRoute\RouteCollector;
use App\Controllers\TestGetController;
use App\Controllers\TestPostController;

class RoutesCollection
{
    /**
     * Registers APP routes list.
     *
     * @param RouteCollector $r
     */
    public function __invoke(RouteCollector $r)
    {
        $r->addRoute('GET', '/test-get', new TestGetController());
        $r->addRoute('POST', '/test-post/[{title}]', new TestPostController());
    }
}
