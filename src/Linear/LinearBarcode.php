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

/**
 * A linear (one-dimensional) barcode symbol.
 */
interface LinearBarcode
{
    /**
     * The module pattern of the whole symbol, including guard and gap modules.
     *
     * @return list<int> Each element is 1 (bar) or 0 (space).
     */
    public function barcode(): array;

    /**
     * The normalized code that the symbol encodes (including the check digit where applicable).
     */
    public function code(): string;

    /**
     * The check digit for the code, or 0 for symbologies without one.
     */
    public function calculateCheckDigit(): int;
}
