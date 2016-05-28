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
    public function testIsInjectable()
    {
        $this->assertIsInjectable(ThrottlingMiddleware::class);
    }
}
