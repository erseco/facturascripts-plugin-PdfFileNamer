# CLAUDE.md - Development Guide for PdfFileNamer Plugin

This document contains the conventions, coding styles, and best practices for developing FacturaScripts plugins.

## Plugin Structure

```
PdfFileNamer/
├── Extension/
│   └── Lib/
│       └── Export/
│           └── PDFExport.php
├── Lib/
│   └── FilenameBuilder.php
├── XMLView/
│   └── SettingsPdfFileNamer.xml
├── Translation/
│   ├── en_EN.json
│   └── es_ES.json
├── Test/
│   └── main/
│       ├── InitTest.php
│       └── FilenameBuilderTest.php
├── Init.php
├── README.md
├── LICENSE
└── facturascripts.ini
```

## Naming Conventions

| Element | Convention | Example |
|---------|------------|---------|
| Classes | PascalCase | `Init`, `FilenameBuilder` |
| Methods | camelCase | `init()`, `extractTokens()` |
| Properties | camelCase | `$initialized` |
| Constants | UPPER_SNAKE_CASE | `const MAX_LENGTH = 200` |
| PHP Files | PascalCase.php | `Init.php` |
| Translation Keys | kebab-case | `pdf-filename-pattern-help` |

### Namespaces

```php
// Main plugin namespace
namespace FacturaScripts\Plugins\PdfFileNamer;

// Extensions
namespace FacturaScripts\Plugins\PdfFileNamer\Extension\Lib\Export;

// Libraries
namespace FacturaScripts\Plugins\PdfFileNamer\Lib;
```

## PHP Coding Style (PSR-12)

### Basic Rules

- **Indentation:** 4 spaces (NOT tabs)
- **Max line length:** 120 characters
- **Arrays:** Short syntax `[]`, NOT `array()`
- **Strings:** Single quotes preferred
- **Trailing comma:** In multiline arrays
- **Strict types:** Use `declare(strict_types=1)`

### Init.php Example

```php
<?php

declare(strict_types=1);

/**
 * This file is part of PdfFileNamer plugin for FacturaScripts
 * Copyright (C) 2026 Ernesto Serrano <info@ernesto.es>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 */

namespace FacturaScripts\Plugins\PdfFileNamer;

use FacturaScripts\Core\Template\InitClass;
use FacturaScripts\Plugins\PdfFileNamer\Extension\Lib\Export\PDFExport;

class Init extends InitClass
{
    public function init(): void
    {
        $this->loadExtension(new PDFExport());
    }

    public function update(): void
    {
    }

    public function uninstall(): void
    {
    }
}
```

### Extension Pattern

Extensions hook into core FacturaScripts classes. The extension class must be in the correct namespace path matching the target class.

```php
<?php

declare(strict_types=1);

namespace FacturaScripts\Plugins\PdfFileNamer\Extension\Lib\Export;

use Closure;

class PDFExport
{
    public function addBusinessDocPage(): Closure
    {
        return function ($model) {
            // Hook code runs in the context of the original PDFExport class
            // $this refers to the PDFExport instance
        };
    }
}
```

## Settings

Access plugin settings via `Tools::settings()`:

```php
use FacturaScripts\Core\Tools;

// Get a setting
$pattern = Tools::settings('pdffilenamer', 'pattern_FacturaCliente', '');

// Settings are stored in the database when the user saves the configuration form
```

## XMLView Settings

Create `XMLView/SettingsPdfFileNamer.xml` for the settings form:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<view>
    <columns>
        <group name="group-name" title="group-title" icon="fas fa-icon" numcolumns="12">
            <column name="field_name" numcolumns="6" order="100">
                <widget type="text" fieldname="field_name" maxlength="200"/>
            </column>
        </group>
    </columns>
</view>
```

## Translations

### JSON Format

```json
{
    "pdffilenamer": "PDF Filenames",
    "pattern_FacturaCliente": "Pattern for customer invoices"
}
```

### Usage in PHP

```php
Tools::lang()->trans('pdffilenamer')
```

## Useful Commands

```bash
# Check code style
make lint

# Fix code style automatically
make format

# Run tests
make test
```

## References

- **FacturaScripts Core:** `./facturascripts/Core/`
- **Official Documentation:** https://facturascripts.com/documentacion
