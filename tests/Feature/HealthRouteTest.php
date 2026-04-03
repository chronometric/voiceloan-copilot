<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthRouteTest extends TestCase
{
    public function test_up_endpoint_returns_success(): void
    {
        $this->get('/up')->assertOk();
    }
}
