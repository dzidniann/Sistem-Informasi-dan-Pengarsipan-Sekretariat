<?php

namespace Tests\Feature;

use App\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class TrustedProxyConfigurationTest extends TestCase
{
    public function test_production_provider_does_not_force_an_http_backend_to_https(): void
    {
        $this->app['env'] = 'production';
        (new \App\Providers\AppServiceProvider($this->app))->boot();
        \Illuminate\Support\Facades\URL::setRequest(Request::create('http://127.0.0.1:18500/login'));
        $this->assertSame('http://127.0.0.1:18500/login', url('/login'));
    }

    public function test_configured_service_hostname_resolves_without_a_fixed_subnet(): void
    {
        config(['proxies.trusted' => [], 'proxies.hosts' => ['localhost']]);
        $request = Request::create('http://simarta.test/login', 'GET', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);
        (new TrustProxies)->handle($request, function ($request) {
            $this->assertTrue($request->isSecure());

            return new Response;
        });
    }

    public function test_untrusted_client_cannot_spoof_https_or_host(): void
    {
        config(['proxies.trusted' => ['172.29.237.10']]);
        $request = Request::create('http://simarta.test/login', 'GET', [], [], [], [
            'REMOTE_ADDR' => '192.0.2.99',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'evil.test',
        ]);
        (new TrustProxies)->handle($request, function ($request) {
            $this->assertFalse($request->isSecure());
            $this->assertSame('simarta.test', $request->getHost());

            return new Response;
        });
    }

    public function test_only_configured_nginx_can_report_https(): void
    {
        config(['proxies.trusted' => ['172.29.237.10']]);
        $request = Request::create('http://simarta.test/login', 'GET', [], [], [], [
            'REMOTE_ADDR' => '172.29.237.10',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);
        (new TrustProxies)->handle($request, function ($request) {
            $this->assertTrue($request->isSecure());

            return new Response;
        });
    }
}
