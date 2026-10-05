<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteControllersExistTest extends TestCase
{
    public function test_every_registered_controller_route_resolves_to_a_real_method(): void
    {
        foreach (Route::getRoutes() as $route) {
            $action = $route->getAction();
            $controller = $action['controller'] ?? null;

            if (!is_string($controller)) {
                continue;
            }

            [$class, $method] = array_pad(explode('@', $controller), 2, '__invoke');

            if (str_starts_with($class, 'Illuminate\\')) {
                continue;
            }

            $this->assertTrue(class_exists($class), "Controller class {$class} does not exist.");
            $this->assertTrue(method_exists($class, $method), "{$class}::{$method} does not exist.");
        }
    }
}
