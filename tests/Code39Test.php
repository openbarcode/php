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
use OpenBarcode\Linear\Code39;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Code39Test extends TestCase
{
    /** Module pattern of Code 39 "CODE39" including start/stop and inter-character gaps. */
    private const CODE39_BITS = '1001011011010110110100101011010110100101010110010110110101100101011011001010101011001011010100101101101';

    /** Module pattern of Code 39 "HELLO WORLD" including start/stop and inter-character gaps. */
    private const HELLO_WORLD_BITS = '100101101101011010100110101101011001010101101010011010110101001101101011010010100110101101011001101010101101011010010110101011001010110101001101010110010110100101101101';

    public function testCode39VectorMatches(): void
    {
        $bars = (new Code39('CODE39'))->barcode();

        $this->assertCount(103, $bars);
        $this->assertSame(self::CODE39_BITS, implode('', $bars));
    }

    public function testCode39EdgeBitsMatchVector(): void
    {
        $bars = implode('', (new Code39('CODE39'))->barcode());
        $this->assertSame('100101101101011011010010101101', substr($bars, 0, 30));
        $this->assertSame('010101011001011010100101101101', substr($bars, -30));

        $hw = implode('', (new Code39('HELLO WORLD'))->barcode());
        $this->assertSame('100101101101011010100110101101', substr($hw, 0, 30));
        $this->assertSame('001101010110010110100101101101', substr($hw, -30));
    }

    public function testSpaceIsSupported(): void
    {
        $bars = (new Code39('HELLO WORLD'))->barcode();

        $this->assertCount(168, $bars);
        $this->assertSame(self::HELLO_WORLD_BITS, implode('', $bars));
    }

    public function testInputIsUppercased(): void
    {
        $lower = new Code39('code39');

        $this->assertSame('CODE39', $lower->code());
        $this->assertSame(self::CODE39_BITS, implode('', $lower->barcode()));
    }

    public function testEverySupportedCharacterIsEncodedAsTwelveModules(): void
    {
        $supported = str_split('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%');

        foreach ($supported as $char) {
            // "*" + char + "*" is three 12-module symbols joined by two gap modules.
            $bars = (new Code39($char))->barcode();
            $this->assertCount(38, $bars, "character '$char'");
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unsupportedInputs(): iterable
    {
        yield 'at sign' => ['AB@C'];
        yield 'exclamation' => ['HELLO!'];
        yield 'asterisk in data' => ['AB*C'];
        yield 'newline' => ["AB\nC"];
        yield 'non-ascii' => ['CAFÉ'];
    }

    #[DataProvider('unsupportedInputs')]
    public function testUnsupportedCharacterThrows(string $code): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Code 39 does not support character');

        new Code39($code);
    }

    public function testEmptyInputThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Code39('');
    }
}
