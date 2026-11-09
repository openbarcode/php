<?php
/**
 * Copyright 2013 Floms, LLC (Yoel Nunez)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

declare(strict_types=1);

namespace OpenBarcode\Tests;

use InvalidArgumentException;
use OpenBarcode\Linear\UPC;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UPCTest extends TestCase
{
    /** Module pattern of UPC-A 036000291452 (95 modules). */
    private const EXPECTED_BITS = '10100011010111101010111100011010001101000110101010110110011101001100110101110010011101101100101';

    public function testElevenDigitsGetsCheckDigitAppended(): void
    {
        $upc = new UPC('03600029145');

        $this->assertSame('036000291452', $upc->code());
        $this->assertSame(2, $upc->calculateCheckDigit());
    }

    public function testModuleSequenceMatchesVector(): void
    {
        $bars = (new UPC('03600029145'))->barcode();

        $this->assertCount(95, $bars);
        $this->assertSame(self::EXPECTED_BITS, implode('', $bars));
    }

    public function testTwelveDigitsWithCorrectCheckDigitGivesSameSymbol(): void
    {
        $upc = new UPC('036000291452');

        $this->assertSame('036000291452', $upc->code());
        $this->assertSame(self::EXPECTED_BITS, implode('', $upc->barcode()));
    }

    public function testWrongCheckDigitThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UPC-A check digit mismatch');

        new UPC('036000291453');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidLengths(): iterable
    {
        yield 'ten digits' => ['0360002914'];
        yield 'thirteen digits' => ['0360002914520'];
        yield 'empty' => [''];
    }

    #[DataProvider('invalidLengths')]
    public function testInvalidLengthThrows(string $code): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UPC-A code must be 11 digits');

        new UPC($code);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function nonDigitInputs(): iterable
    {
        yield 'letter' => ['03600029145X'];
        yield 'space' => ['0360002914 '];
        yield 'sign' => ['+3600029145'];
    }

    #[DataProvider('nonDigitInputs')]
    public function testNonDigitInputThrows(string $code): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UPC-A code must contain only digits 0-9');

        new UPC($code);
    }
}
