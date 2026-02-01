<?php

/**
 * This file is part of PdfFileNamer plugin for FacturaScripts.
 * Copyright (C) 2026 Ernesto Serrano <info@ernesto.es>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 */

declare(strict_types=1);

namespace FacturaScripts\Plugins\PdfFileNamer;

use FacturaScripts\Core\Template\InitClass;
use FacturaScripts\Core\Tools;

/**
 * Plugin initialization class.
 * The PDFExport class in Lib/Export overrides the Core PDFExport
 * to customize PDF filenames based on configured patterns.
 */
class Init extends InitClass
{
    public function init(): void
    {
        // PDFExport is loaded automatically via Dinamic class system
    }

    public function update(): void
    {
        // Initialize settings with empty values to create the settings group
        $settings = [
            'pattern_FacturaCliente',
            'pattern_FacturaProveedor',
            'pattern_PresupuestoCliente',
            'pattern_PedidoCliente',
            'pattern_PedidoProveedor',
            'pattern_AlbaranCliente',
            'pattern_AlbaranProveedor',
        ];

        foreach ($settings as $key) {
            // This creates the setting if it doesn't exist (in memory)
            Tools::settings('pdffilenamer', $key, '');
        }

        // Save settings to database to create the settings group
        Tools::settingsSave();
    }

    public function uninstall(): void
    {
    }
}
