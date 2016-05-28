<?php

/*
 * This file is part of Alt Three Throttle.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AltThree\Throttle\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * This is the throttling middleware class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class ThrottlingMiddleware
{
    /**
     * The rate limiter instance.
     *
     * @var \Illuminate\Cache\RateLimiter
     */
    protected $limiter;

    /**
     * The URIs that should be excluded.
     *
     * @var string[]
     */
    protected $except = [];

    /**
     * Create a new throttling middleware instance.
     *
     * @param \Illuminate\Cache\RateLimiter $limiter
     *
     * @return void
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param int                      $max
     * @param int                      $decay
     *
     * @throws \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $max = 60, $decay = 1, $global = false)
    {
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        $key = $global ? sha1($request->ip()) : $request->fingerprint();

        if ($this->limiter->tooManyAttempts($key, $limit, $decay)) {
            throw $this->buildException($key, $limit);
        }

        $this->limiter->hit($key, $decay);

        $response = $next($request);

        $response->headers->add($this->getHeaders($key, $limit));

        return $response;
    }

    /**
     * Create a too many requests http exception.
     *
     * @param string $key
     * @param int    $limit
     *
     * @return \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
     */
    protected function buildException($key, $limit)
    {
        $exception = new TooManyRequestsHttpException($this->limiter->availableIn($key), 'Rate limit exceeded.');

        $exception->setHeaders($this->getHeaders($key, $limit, $exception->getHeaders()));

        return $exception;
    }

    /**
     * Get the limit header information.
     *
     * @param string $key
     * @param int    $limit
     * @param array  $merge
     *
     * @return array
     */
    protected function getHeaders($key, $limit, array $merge = [])
    {
        $remaining = $limit - $this->limiter->attempts($key) + 1;

        $headers ['X-RateLimit-Limit' => $limit, 'X-RateLimit-Remaining' => $remaining];

        return array_merge($headers, $merge);
    }

    /**
     * Determine if the request has a URI that should pass through.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough(Request $request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
