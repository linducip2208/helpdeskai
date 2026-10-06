<?php

namespace Tests;

use App\Services\LicenseClient;
use Mockery;

trait BypassesPairing
{
    protected function bypassPairing(): void
    {
        $mock = Mockery::mock(LicenseClient::class);
        $mock->shouldReceive('verify')->andReturn(['host' => 'localhost', 'ok' => true]);

        $this->app->instance(LicenseClient::class, $mock);
    }
}
