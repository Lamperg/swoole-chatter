<?php

namespace Tests;

use App\Application;
use App\Kernel\WebsocketServer;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    /**
     * @test
     */
    public function serverCanBeBooted()
    {
        $server = Application::getServer();
        
        $this->assertNotEmpty($server);
        $this->assertInstanceOf(WebsocketServer::class, $server);
    }
}
