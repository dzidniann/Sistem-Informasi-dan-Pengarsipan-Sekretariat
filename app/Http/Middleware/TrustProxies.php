<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = [];

    public function handle(Request $request, \Closure $next)
    {
        $this->proxies = config('proxies.trusted', []);

        // Resolve deployment-controlled service names, never request headers.
        // Docker may reassign addresses when a container is recreated.
        foreach (config('proxies.hosts', []) as $host) {
            $addresses = gethostbynamel($host);
            if ($addresses !== false) {
                $this->proxies = array_merge($this->proxies, $addresses);
            }
        }

        return parent::handle($request, $next);
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO;
}
