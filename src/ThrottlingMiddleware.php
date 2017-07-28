<?php

/*
 * This file is part of Alt Three Throttle.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AltThree\Throttle;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

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
     * @param int                      $limit
     * @param float|int                $decay
     * @param bool                     $global
     * @param bool                     $headers
     *
     * @throws \AltThree\Throttle\ThrottlingException
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $limit = 60, $decay = 1, $global = false, $headers = true)
    {
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        $key = $global ? sha1($request->ip()) : $request->fingerprint();

        if ($this->limiter->tooManyAttempts($key, $limit, $decay)) {
            throw $this->buildException($key, $limit, $headers);
        }

        $this->limiter->hit($key, $decay);

        $response = $next($request);

        $response->headers->add($this->getHeaders($key, $limit, $headers));

        return $response;
    }

    /**
     * Create a too many requests http exception.
     *
     * @param string $key
     * @param int    $limit
     * @param bool   $headers
     *
     * @return \AltThree\Throttle\ThrottlingException
     */
    protected function buildException(string $key, int $limit, bool $headers)
    {
        $after = $this->limiter->availableIn($key);

        $exception = new ThrottlingException($after, 'Rate limit exceeded.');

        $headers = $this->getHeaders($key, $limit, $headers, $after, $exception->getHeaders());

        $exception->setHeaders($headers);

        return $exception;
    }

    /**
     * Get the limit header information.
     *
     * @param string   $key
     * @param int      $limit
     * @param bool     $add
     * @param int|null $after
     * @param array    $merge
     *
     * @return array
     */
    protected function getHeaders(string $key, int $limit, bool $add = true, int $after = null, array $merge = [])
    {
        $remaining = $after === null ? $this->limiter->retriesLeft($key, $limit) : 0;

        $headers = $add ? ['X-RateLimit-Limit' => $limit, 'X-RateLimit-Remaining' => $remaining] : [];

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
