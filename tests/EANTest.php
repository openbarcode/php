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
use OpenBarcode\Linear\EAN;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EANTest extends TestCase
{
    /** Module pattern of EAN-13 4006381333931 (95 modules). */
    private const EXPECTED_BITS = '10100011010100111010111101111010001001011001101010100001010000101000010111010010000101100110101';

    /**
     * GS1 left-hand parity table: which set (L or G) each of the six left digits uses,
     * indexed by the first (number system) digit.
     */
    private const PARITY = [
        '0' => 'LLLLLL', '1' => 'LLGLGG', '2' => 'LLGGLG', '3' => 'LLGGGL', '4' => 'LGLLGG',
        '5' => 'LGGLLG', '6' => 'LGGGLL', '7' => 'LGLGLG', '8' => 'LGLGGL', '9' => 'LGGLGL',
    ];

    public function testTwelveDigitsComputesCheckDigit(): void
    {
        $ean = new EAN('400638133393');

        $this->assertSame('4006381333931', $ean->code());
        $this->assertSame(1, $ean->calculateCheckDigit());
    }

    public function testModuleSequenceMatchesVector(): void
    {
        $bars = (new EAN('400638133393'))->barcode();

        $this->assertCount(95, $bars);
        $this->assertSame(self::EXPECTED_BITS, implode('', $bars));
    }

    public function testThirteenDigitsWithCorrectCheckDigitGivesSameSymbol(): void
    {
        $ean = new EAN('4006381333931');

        $this->assertSame(self::EXPECTED_BITS, implode('', $ean->barcode()));
    }

    public function testWrongCheckDigitThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('EAN-13 check digit mismatch');

        new EAN('4006381333932');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidLengths(): iterable
    {
        yield 'eleven digits' => ['40063813339'];
        yield 'fourteen digits' => ['40063813339312'];
        yield 'empty' => [''];
    }

    #[DataProvider('invalidLengths')]
    public function testInvalidLengthThrows(string $code): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('EAN-13 code must be 12 digits');

        new EAN($code);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function nonDigitInputs(): iterable
    {
        yield 'letter' => ['40063813339A'];
        yield 'hyphen' => ['4006381-3393'];
    }

    #[DataProvider('nonDigitInputs')]
    public function testNonDigitInputThrows(string $code): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('EAN-13 code must contain only digits 0-9');

        new EAN($code);
    }

    public function testFirstDigitSelectsLeftParityPattern(): void
    {
        $lSet = ['0001101', '0011001', '0010011', '0111101', '0100011', '0110001', '0101111', '0111011', '0110111', '0001011'];
        $gSet = ['0100111', '0110011', '0011011', '0100001', '0011101', '0111001', '0000101', '0010001', '0001001', '0010111'];

        for ($first = 0; $first <= 9; $first++) {
            // Left digits are all zero, so each six-digit group is simply the L or G encoding of 0.
            $bars = implode('', (new EAN($first . '00000000000'))->barcode());
            $parity = self::PARITY[(string) $first];

            for ($i = 0; $i < 6; $i++) {
                $chunk = substr($bars, 3 + 7 * $i, 7);
                $expected = $parity[$i] === 'L' ? $lSet[0] : $gSet[0];
                $this->assertSame($expected, $chunk, "first digit $first, left digit " . ($i + 1));
            }
        }
    }
}
