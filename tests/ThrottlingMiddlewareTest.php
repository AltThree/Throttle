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
use Illuminate\Support\Facades\Route;

/**
 * This is the throttling middleware test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class ThrottlingMiddlewareTest extends AbstractPackageTestCase
{
    /**
     * Middleware to test.
     *
     * @var ThrottlingMiddleware
     */
    protected $middleware;

    public function setUp()
    {
        parent::setUp();

        // Set up a fake route.
        Route::get('/dummy', ['middleware' => ThrottlingMiddleware::class, function () {
            return 'success';
        }]);
    }

    public function testIsInjectable()
    {
        $this->assertIsInjectable(ThrottlingMiddleware::class);
    }

    public function testHandleSuccess()
    {
        $response = $this->call('GET', '/dummy');
        $this->assertEquals(200, $response->status());
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
     */
    public function testTooManyRequestsHttpException()
    {
        for ($i = 1; $i <= 61; $i++) {
            $this->call('GET', '/dummy');
        }
    }
}
