<?php

declare(strict_types=1);

/*
 * This file is part of Alt Three Throttle.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AltThree\Throttle;

use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * This is the throttling exception class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class ThrottlingException extends TooManyRequestsHttpException
{
    //
}
