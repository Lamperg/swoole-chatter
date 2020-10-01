<?php

namespace Tests;

use App\Runner;
use App\RunnerFactory;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    /**
     * @test
     */
    public function example()
    {
        $app = RunnerFactory::create();
        $this->assertInstanceOf(Runner::class, $app);
    }
}
