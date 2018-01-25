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

namespace AltThree\Tests\Throttle;

use AltThree\Throttle\TypeUtil;
use PHPUnit\Framework\TestCase;

/**
 * This is the analysis test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class TypeUtilTest extends TestCase
{
    /**
     * @dataProvider providesNumericCases
     */
    public function testConvertNumeric($input, $output)
    {
        $this->assertSame($output, TypeUtil::convertNumeric($input));
    }

    public function providesNumericCases()
    {
        return [
            ['', 0],
            ['0', 0],
            ['0.0', 0.0],
            ['1', 1],
            ['123', 123],
            [2, 2],
            [3.3, 3.3],
        ];
    }

    /**
     * @dataProvider providesBooleanCases
     */
    public function testConvertBoolean($input, $output)
    {
        $this->assertSame($output, TypeUtil::convertBoolean($input));
    }

    public function providesBooleanCases()
    {
        return [
            ['', false],
            ['false', false],
            ['true', true],
            ['0', false],
            ['1', true],
            [false, false],
            [true, true],
        ];
    }
}
