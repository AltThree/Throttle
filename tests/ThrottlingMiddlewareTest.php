<?php

/*
 * This file is part of Alt Three Throttle.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AltThree\Tests\Throttle;

use AltThree\Throttle\ThrottlingMiddleware;
use GrahamCampbell\TestBench\AbstractPackageTestCase;

/**
 * This is the throttling middleware test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class ThrottlingMiddlewareTest extends AbstractPackageTestCase
{
    /**
     * @before
     */
    public function setUpDummyRoute()
    {
        $this->app->router->get('/dummy', ['middleware' => ThrottlingMiddleware::class, function () {
            return 'success';
        }]);
    }

    public function testIsInjectable()
    {
        $this->assertIsInjectable(ThrottlingMiddleware::class);
    }

    public function testHandleSuccess()
    {
        for ($i = 59; $i >= 1; $i--) {
            $response = $this->call('GET', '/dummy');
            $this->assertSame(200, $response->status());
            $this->assertSame(60, $response->headers->get('x-ratelimit-limit'));
            $this->assertSame($i, $response->headers->get('x-ratelimit-remaining'));
        }
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
     * @expectedExceptionMessage Rate limit exceeded.
     */
    public function testTooManyRequestsHttpException()
    {
        for ($i = 1; $i <= 61; $i++) {
            $this->call('GET', '/dummy');
        }
    }
}
