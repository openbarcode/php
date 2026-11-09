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
 * UPC-A barcode.
 *
 * Accepts 11 digits (the check digit is computed) or 12 digits (the check digit is verified).
 * UPC-A is encoded as EAN-13 with a leading zero, so the module pattern is produced by EAN.
 */
final class UPC implements LinearBarcode
{
    private readonly string $code;

    /** @var list<int> */
    private readonly array $bars;

    public function __construct(string $code)
    {
        if (preg_match('/\A[0-9]*\z/', $code) !== 1) {
            throw new InvalidArgumentException('UPC-A code must contain only digits 0-9.');
        }

        $length = strlen($code);
        if ($length !== 11 && $length !== 12) {
            throw new InvalidArgumentException(sprintf(
                'UPC-A code must be 11 digits (check digit computed) or 12 digits (check digit verified), got %d.',
                $length
            ));
        }

        $body = substr($code, 0, 11);
        $check = self::checkDigit($body);

        if ($length === 12 && (int) $code[11] !== $check) {
            throw new InvalidArgumentException(sprintf(
                'UPC-A check digit mismatch: expected %d, got %s.',
                $check,
                $code[11]
            ));
        }

        $this->code = $body . $check;
        $this->bars = (new EAN('0' . $this->code))->barcode();
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
        return self::checkDigit(substr($this->code, 0, 11));
    }

    /**
     * Weights 3 and 1 alternate from the left; the check digit brings the total to a multiple of 10.
     */
    private static function checkDigit(string $elevenDigits): int
    {
        $sum = 0;
        for ($i = 0; $i < 11; $i++) {
            $digit = (int) $elevenDigits[$i];
            $sum += ($i % 2 === 0) ? $digit * 3 : $digit;
        }

        return (10 - $sum % 10) % 10;
    }
}
