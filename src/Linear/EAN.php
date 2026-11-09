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

namespace OpenBarcode\Linear;

use InvalidArgumentException;

/**
 * EAN-13 barcode.
 *
 * Accepts 12 digits (the check digit is computed) or 13 digits (the check digit is verified).
 */
final class EAN implements LinearBarcode
{
    private const START_GUARD = [1, 0, 1];
    private const MIDDLE_GUARD = [0, 1, 0, 1, 0];
    private const END_GUARD = [1, 0, 1];

    /** Left-hand digits with odd parity (GS1 "L" set). */
    private const L = [
        '0' => [0, 0, 0, 1, 1, 0, 1],
        '1' => [0, 0, 1, 1, 0, 0, 1],
        '2' => [0, 0, 1, 0, 0, 1, 1],
        '3' => [0, 1, 1, 1, 1, 0, 1],
        '4' => [0, 1, 0, 0, 0, 1, 1],
        '5' => [0, 1, 1, 0, 0, 0, 1],
        '6' => [0, 1, 0, 1, 1, 1, 1],
        '7' => [0, 1, 1, 1, 0, 1, 1],
        '8' => [0, 1, 1, 0, 1, 1, 1],
        '9' => [0, 0, 0, 1, 0, 1, 1],
    ];

    /** Left-hand digits with even parity (GS1 "G" set). */
    private const G = [
        '0' => [0, 1, 0, 0, 1, 1, 1],
        '1' => [0, 1, 1, 0, 0, 1, 1],
        '2' => [0, 0, 1, 1, 0, 1, 1],
        '3' => [0, 1, 0, 0, 0, 0, 1],
        '4' => [0, 0, 1, 1, 1, 0, 1],
        '5' => [0, 1, 1, 1, 0, 0, 1],
        '6' => [0, 0, 0, 0, 1, 0, 1],
        '7' => [0, 0, 1, 0, 0, 0, 1],
        '8' => [0, 0, 0, 1, 0, 0, 1],
        '9' => [0, 0, 1, 0, 1, 1, 1],
    ];

    /** Right-hand digits (GS1 "R" set). */
    private const R = [
        '0' => [1, 1, 1, 0, 0, 1, 0],
        '1' => [1, 1, 0, 0, 1, 1, 0],
        '2' => [1, 1, 0, 1, 1, 0, 0],
        '3' => [1, 0, 0, 0, 0, 1, 0],
        '4' => [1, 0, 1, 1, 1, 0, 0],
        '5' => [1, 0, 0, 1, 1, 1, 0],
        '6' => [1, 0, 1, 0, 0, 0, 0],
        '7' => [1, 0, 0, 0, 1, 0, 0],
        '8' => [1, 0, 0, 1, 0, 0, 0],
        '9' => [1, 1, 1, 0, 1, 0, 0],
    ];

    /**
     * Parity pattern of the six left-hand digits, indexed by the number system (first) digit.
     * "L" selects table L, "G" selects table G.
     */
    private const PARITY = [
        '0' => 'LLLLLL',
        '1' => 'LLGLGG',
        '2' => 'LLGGLG',
        '3' => 'LLGGGL',
        '4' => 'LGLLGG',
        '5' => 'LGGLLG',
        '6' => 'LGGGLL',
        '7' => 'LGLGLG',
        '8' => 'LGLGGL',
        '9' => 'LGGLGL',
    ];

    private readonly string $code;

    /** @var list<int> */
    private readonly array $bars;

    public function __construct(string $code)
    {
        if (preg_match('/\A[0-9]*\z/', $code) !== 1) {
            throw new InvalidArgumentException('EAN-13 code must contain only digits 0-9.');
        }

        $length = strlen($code);
        if ($length !== 12 && $length !== 13) {
            throw new InvalidArgumentException(sprintf(
                'EAN-13 code must be 12 digits (check digit computed) or 13 digits (check digit verified), got %d.',
                $length
            ));
        }

        $body = substr($code, 0, 12);
        $check = self::checkDigit($body);

        if ($length === 13 && (int) $code[12] !== $check) {
            throw new InvalidArgumentException(sprintf(
                'EAN-13 check digit mismatch: expected %d, got %s.',
                $check,
                $code[12]
            ));
        }

        $this->code = $body . $check;
        $this->bars = self::encode($this->code);
    }

    public function barcode(): array
    {
        return $this->bars;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function calculateCheckDigit(): int
    {
        return self::checkDigit(substr($this->code, 0, 12));
    }

    /**
     * Weights 1 and 3 alternate from the left; the check digit brings the total to a multiple of 10.
     */
    private static function checkDigit(string $twelveDigits): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $twelveDigits[$i];
            $sum += ($i % 2 === 1) ? $digit * 3 : $digit;
        }

        return (10 - $sum % 10) % 10;
    }

    /**
     * @return list<int>
     */
    private static function encode(string $thirteenDigits): array
    {
        $parity = self::PARITY[$thirteenDigits[0]];
        $bars = self::START_GUARD;

        for ($i = 1; $i <= 6; $i++) {
            $table = $parity[$i - 1] === 'L' ? self::L : self::G;
            array_push($bars, ...$table[$thirteenDigits[$i]]);
        }

        array_push($bars, ...self::MIDDLE_GUARD);

        for ($i = 7; $i <= 12; $i++) {
            array_push($bars, ...self::R[$thirteenDigits[$i]]);
        }

        array_push($bars, ...self::END_GUARD);

        return $bars;
    }
}
