<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Middleware\ForceHttps;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForceHttpsMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_middleware_allows_http_when_force_https_disabled(): void
    {
        config(['app.force_https' => false]);

        $request = Request::create('http://example.com/test');
        $middleware = new ForceHttps();

        $response = $middleware->handle($request, function($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_redirects_http_when_force_https_enabled(): void
    {
        config(['app.force_https' => true]);

        $request = Request::create('http://example.com/test');
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['SERVER_PORT'] = '80';

        $middleware = new ForceHttps();

        $response = $middleware->handle($request, function($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertStringContainsString('https://', $response->headers->get('Location'));
    }

    public function test_middleware_allows_https_when_force_https_enabled(): void
    {
        config(['app.force_https' => true]);

        $request = Request::create('https://example.com/test');
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['SERVER_PORT'] = '443';

        $middleware = new ForceHttps();

        $response = $middleware->handle($request, function($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_detects_https_via_x_forwarded_proto(): void
    {
        config(['app.force_https' => true]);

        $request = Request::create('http://example.com/test');
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test';

        $middleware = new ForceHttps();

        $response = $middleware->handle($request, function($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_detects_https_via_port_443(): void
    {
        config(['app.force_https' => true]);

        $request = Request::create('http://example.com/test');
        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test';

        $middleware = new ForceHttps();

        $response = $middleware->handle($request, function($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }


    public function test_middleware_does_not_force_in_local_environment(): void
    {
        // Mock local environment
        app()->detectEnvironment(function() {
            return 'local';
        });

        config(['app.force_https' => null]); // Not set, should default to production (but we're in local)

        $request = Request::create('http://example.com/test');

        $middleware = new ForceHttps();

        $response = $middleware->handle($request, function($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }
}
