<?php

namespace NspTeam\WebmanThrottler\Middleware;


use NspTeam\WebmanThrottler\Throttle\Throttler;
use support\Cache;
use support\Container;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;


/**
 * ThrottlerMiddleware
 */
class ThrottlerMiddleware implements MiddlewareInterface
{

    /**
     * @inheritDoc
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        /**
         * @var Throttler
         */
        $throttler = Container::make(Throttler::class, [Cache::instance()]);

        $config = config('plugin.nsp-team.webman-throttler.app');
        $capacity = $config['capacity'] ?? 60;
        $seconds = $config['seconds'] ?? 60;
        $cost = $config['cost'] ?? 1;

        if ($throttler->check($request->getRemoteIp(), $capacity, $seconds, $cost) === false) {
            return new Response(429, ['Content-Type' => 'application/json'], json_encode(['success' => false, 'msg' => '请求此时太频繁'], JSON_UNESCAPED_UNICODE));
        }

        return $handler($request);
    }
}