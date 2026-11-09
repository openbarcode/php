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

use OpenBarcode\Linear\Code39;
use OpenBarcode\Linear\EAN;
use OpenBarcode\Linear\UPC;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Regression tests: barcode objects must not share mutable state with each other
 * (the Python port once kept a class-level bars list that every instance appended to).
 */
final class InstanceIsolationTest extends TestCase
{
    public function testConstructingOtherInstancesDoesNotChangeEarlierOnes(): void
    {
        $first = new EAN('400638133393');
        $before = $first->barcode();

        new EAN('590123412345');
        new UPC('03600029145');
        new Code39('HELLO WORLD');

        $this->assertSame($before, $first->barcode());
        $this->assertSame(implode('', (new EAN('400638133393'))->barcode()), implode('', $first->barcode()));
    }

    public function testInstancesWithSameInputAreEqualButIndependent(): void
    {
        $a = new Code39('CODE39');
        $b = new Code39('CODE39');

        $this->assertSame($a->barcode(), $b->barcode());
        $this->assertSame(103, count($a->barcode()));
        $this->assertSame(103, count($b->barcode()));
    }

    public function testReturnedArrayIsACopy(): void
    {
        $upc = new UPC('03600029145');
        $bars = $upc->barcode();
        $bars[0] = 0;

        $this->assertSame(1, $upc->barcode()[0]);
    }

    public function testNoStaticPropertiesHoldBarcodeState(): void
    {
        foreach ([UPC::class, EAN::class, Code39::class] as $class) {
            $this->assertSame([], (new ReflectionClass($class))->getStaticProperties(), $class);
        }
    }
}
