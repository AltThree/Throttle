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
    /**
     * Set response headers.
     *
     * @param array $headers
     *
     * @return void
     */
    public function setHeaders(array $headers)
    {
        // this was new in symfony 3.1
        if (method_exists(get_parent_class($this), 'setHeaders')) {
            return $this->setHeaders($headers);
        }

        $property = (new ReflectionClass(HttpException::class))->getProperty('headers');
        $property->setAccessible(true);
        $property->setValue($this, $headers);
    }
}
