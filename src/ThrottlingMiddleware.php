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
use ReflectionClass;
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
     * @param int                      $limit
     * @param int                      $decay
     * @param bool                     $global
     * @param bool                     $headers
     *
     * @throws \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
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
     * @return \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
     */
    protected function buildException($key, $limit, $headers)
    {
        $exception = new TooManyRequestsHttpException($this->limiter->availableIn($key), 'Rate limit exceeded.');

        $headers = $this->getHeaders($key, $limit, $headers, $exception->getHeaders());

        // this was new in symfony 3.1
        if (method_exists($exception, 'setHeaders')) {
            $exception->setHeaders($headers);
        } else {
            $property = (new ReflectionClass($exception))->getProperty('headers');
            $property->setAccessible(true);
            $property->setValue($exception, $headers);
        }

        return $exception;
    }

    /**
     * Get the limit header information.
     *
     * @param string $key
     * @param int    $limit
     * @param bool   $add
     * @param array  $merge
     *
     * @return array
     */
    protected function getHeaders($key, $limit, $add = true, array $merge = [])
    {
        $remaining = $limit - $this->limiter->attempts($key) + 1;

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
