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
use FacturaScripts\Plugins\PdfFileNamer\Extension\Controller\EditController;
use FacturaScripts\Plugins\PdfFileNamer\Extension\Lib\Export\PDFExport;

/**
 * Plugin initialization class.
 */
class Init extends InitClass
{
    public function init(): void
    {
        // EditController is the compatibility path used when another plugin replaces PDFExport.
        $this->loadExtension(new EditController());

        // Native PDFExport supports extensions through PDFDocument::ExtensionsTrait.
        // Some plugins, such as PlantillasPDF, replace the dynamic PDFExport class with
        // an implementation that does not expose addExtension(). In that case, trying
        // to register our PDFExport extension breaks the plugin rebuild process.
        $pdfExportClass = '\\FacturaScripts\\Dinamic\\Lib\\Export\\PDFExport';
        if (class_exists($pdfExportClass) && method_exists($pdfExportClass, 'addExtension')) {
            $this->loadExtension(new PDFExport());
        }
    }

    public function update(): void
    {
        // Initialize settings with empty values to create the settings group.
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
            // This creates the setting if it doesn't exist in memory.
            Tools::settings('pdffilenamer', $key, '');
        }

        // Save settings to database to create the settings group.
        Tools::settingsSave();
    }

    public function uninstall(): void
    {
    }
}
