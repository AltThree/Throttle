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

/**
 * This is the type util class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class TypeUtil
{
    /**
     * Convert the given value to numeric.
     *
     * @param int|float|string $v
     *
     * @return int|float
     */
    public static function convertNumeric($v)
    {
        return $v === '' ? 0 : $v + 0;
    }

    /**
     * Convert the given value to boolean.
     *
     * @param bool|string $v
     *
     * @return bool
     */
    public static function convertBoolean($v)
    {
        return $v === 'false' ? false : (bool) $v;
    }
}
