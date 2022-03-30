<?php

namespace NspTeam\WebmanThrottler\Middleware;


use NspTeam\WebmanThrottler\Throttle\Throttler;
use support\Cache;
use support\Container;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

define('MINUTE', 600);

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

        if ($throttler->check($request->getRemoteIp(), 10, MINUTE, 1) === false) {
            return new Response(429, ['Content-Type' => 'application/json'], json_encode(['success' => false, 'msg' => '请求此时太频繁'], JSON_UNESCAPED_UNICODE));
        }

        return $handler($request);
    }
}