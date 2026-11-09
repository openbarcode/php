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
 * Code 39 barcode (ISO/IEC 16388), standard character set.
 *
 * Input is converted to upper case. Supported characters are 0-9, A-Z, space, and
 * - . $ / + %. The start/stop character "*" is added automatically and is not accepted as data.
 */
final class Code39 implements LinearBarcode
{
    /**
     * Each symbol is 9 elements (5 bars, 4 spaces) with 3 wide elements, written as 12 modules
     * (wide element = 2 modules, narrow = 1). Starts with a bar.
     */
    private const PATTERNS = [
        '0' => [1, 0, 1, 0, 0, 1, 1, 0, 1, 1, 0, 1],
        '1' => [1, 1, 0, 1, 0, 0, 1, 0, 1, 0, 1, 1],
        '2' => [1, 0, 1, 1, 0, 0, 1, 0, 1, 0, 1, 1],
        '3' => [1, 1, 0, 1, 1, 0, 0, 1, 0, 1, 0, 1],
        '4' => [1, 0, 1, 0, 0, 1, 1, 0, 1, 0, 1, 1],
        '5' => [1, 1, 0, 1, 0, 0, 1, 1, 0, 1, 0, 1],
        '6' => [1, 0, 1, 1, 0, 0, 1, 1, 0, 1, 0, 1],
        '7' => [1, 0, 1, 0, 0, 1, 0, 1, 1, 0, 1, 1],
        '8' => [1, 1, 0, 1, 0, 0, 1, 0, 1, 1, 0, 1],
        '9' => [1, 0, 1, 1, 0, 0, 1, 0, 1, 1, 0, 1],
        'A' => [1, 1, 0, 1, 0, 1, 0, 0, 1, 0, 1, 1],
        'B' => [1, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1, 1],
        'C' => [1, 1, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1],
        'D' => [1, 0, 1, 0, 1, 1, 0, 0, 1, 0, 1, 1],
        'E' => [1, 1, 0, 1, 0, 1, 1, 0, 0, 1, 0, 1],
        'F' => [1, 0, 1, 1, 0, 1, 1, 0, 0, 1, 0, 1],
        'G' => [1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 1, 1],
        'H' => [1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 1],
        'I' => [1, 0, 1, 1, 0, 1, 0, 0, 1, 1, 0, 1],
        'J' => [1, 0, 1, 0, 1, 1, 0, 0, 1, 1, 0, 1],
        'K' => [1, 1, 0, 1, 0, 1, 0, 1, 0, 0, 1, 1],
        'L' => [1, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1],
        'M' => [1, 1, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1],
        'N' => [1, 0, 1, 0, 1, 1, 0, 1, 0, 0, 1, 1],
        'O' => [1, 1, 0, 1, 0, 1, 1, 0, 1, 0, 0, 1],
        'P' => [1, 0, 1, 1, 0, 1, 1, 0, 1, 0, 0, 1],
        'Q' => [1, 0, 1, 0, 1, 0, 1, 1, 0, 0, 1, 1],
        'R' => [1, 1, 0, 1, 0, 1, 0, 1, 1, 0, 0, 1],
        'S' => [1, 0, 1, 1, 0, 1, 0, 1, 1, 0, 0, 1],
        'T' => [1, 0, 1, 0, 1, 1, 0, 1, 1, 0, 0, 1],
        'U' => [1, 1, 0, 0, 1, 0, 1, 0, 1, 0, 1, 1],
        'V' => [1, 0, 0, 1, 1, 0, 1, 0, 1, 0, 1, 1],
        'W' => [1, 1, 0, 0, 1, 1, 0, 1, 0, 1, 0, 1],
        'X' => [1, 0, 0, 1, 0, 1, 1, 0, 1, 0, 1, 1],
        'Y' => [1, 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, 1],
        'Z' => [1, 0, 0, 1, 1, 0, 1, 1, 0, 1, 0, 1],
        '-' => [1, 0, 0, 1, 0, 1, 0, 1, 1, 0, 1, 1],
        '.' => [1, 1, 0, 0, 1, 0, 1, 0, 1, 1, 0, 1],
        ' ' => [1, 0, 0, 1, 1, 0, 1, 0, 1, 1, 0, 1],
        '$' => [1, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 1],
        '/' => [1, 0, 0, 1, 0, 0, 1, 0, 1, 0, 0, 1],
        '+' => [1, 0, 0, 1, 0, 1, 0, 0, 1, 0, 0, 1],
        '%' => [1, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1],
        '*' => [1, 0, 0, 1, 0, 1, 1, 0, 1, 1, 0, 1],
    ];

    private readonly string $code;

    /** @var list<int> */
    private readonly array $bars;

    public function __construct(string $code)
    {
        $upper = strtoupper($code);

        if ($upper === '') {
            throw new InvalidArgumentException('Code 39 input must not be empty.');
        }

        for ($i = 0, $n = strlen($upper); $i < $n; $i++) {
            $char = $upper[$i];
            if ($char === '*' || !isset(self::PATTERNS[$char])) {
                throw new InvalidArgumentException(sprintf(
                    'Code 39 does not support character "%s" at position %d.',
                    $char,
                    $i
                ));
            }
        }

        $this->code = $upper;
        $this->bars = self::encode($upper);
    }

    public function barcode(): array
    {
        return $this->bars;
    }

    public function code(): string
    {
        return $this->code;
    }

    /**
     * Code 39 has no check digit in this implementation; always returns 0.
     */
    public function calculateCheckDigit(): int
    {
        return 0;
    }

    /**
     * Start and stop "*" surround the data. A single narrow space (0) separates adjacent symbols;
     * there is no space before the start symbol or after the stop symbol.
     *
     * @return list<int>
     */
    private static function encode(string $data): array
    {
        $bars = [];
        foreach (str_split('*' . $data . '*') as $index => $char) {
            if ($index > 0) {
                $bars[] = 0;
            }
            array_push($bars, ...self::PATTERNS[$char]);
        }

        return $bars;
    }
}
