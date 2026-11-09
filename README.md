<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="assets/branding/open-barcode-logo-dark.svg">
    <img src="assets/branding/open-barcode-logo.svg" alt="Open Barcode by Floms" width="420">
  </picture>
</p>

<p align="center"><em>Barcodes, made open.</em></p>

# Open Barcode for PHP

Barcode generation library for PHP (Code 39, EAN, UPC).

Open Barcode for PHP builds linear barcode module patterns for Code 39, EAN-13 and UPC-A.
It has no runtime dependencies and needs PHP 8.1 or later.

## Installation

```bash
composer require openbarcode/openbarcode
```

## Usage

All classes live in the `OpenBarcode\Linear` namespace. Each constructor takes the code as a string and
throws `InvalidArgumentException` for invalid input.

```php
<?php

use OpenBarcode\Linear\Code39;
use OpenBarcode\Linear\EAN;
use OpenBarcode\Linear\UPC;

// EAN-13: pass 12 digits to have the check digit computed, or 13 digits to have it verified.
$ean = new EAN('400638133393');
$ean->code();           // "4006381333931"
$ean->barcode();        // list<int> of 95 modules, 1 = bar, 0 = space

// UPC-A: 11 digits (check digit computed) or 12 digits (check digit verified).
$upc = new UPC('03600029145');
$upc->code();           // "036000291452"
$upc->barcode();        // list<int> of 95 modules

// Code 39: input is upper-cased. Supported: 0-9, A-Z, space, - . $ / + %
$code39 = new Code39('Hello World');
$code39->code();        // "HELLO WORLD"
$code39->barcode();     // list<int> including start/stop and gap modules
```

`LinearBarcode` is the interface all three classes implement: `barcode()`, `code()` and
`calculateCheckDigit()`.

## Testing

The test suite uses PHPUnit, which is the only development dependency.

```bash
composer install
vendor/bin/phpunit
```

CI runs the same two commands on PHP 8.1, 8.3 and 8.4 (see `.github/workflows/ci.yml`).
Open Barcode is available in several languages:

- Java: https://github.com/openbarcode/java
- C#: https://github.com/openbarcode/csharp
- Python: https://github.com/openbarcode/python
- PHP: https://github.com/openbarcode/php
- JavaScript: https://github.com/openbarcode/javascript

Project homepage: https://openbarcode.dev

This repository was split from the original multi-language project at
https://github.com/floms/Open-Barcode. The commit history was preserved
with git-filter-repo; commit hashes differ from the original.

Copyright 2013 Floms, LLC (Yoel Nunez)

Copyright 2026 Floms, Inc.

Licensed under the Apache License, Version 2.0. See LICENSE.md for details.
