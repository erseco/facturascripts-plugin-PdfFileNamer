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

namespace FacturaScripts\Plugins\PdfFileNamer\Extension\Lib\Export;

use Closure;
use FacturaScripts\Core\Model\Base\BusinessDocument;
use FacturaScripts\Core\Tools;
use FacturaScripts\Plugins\PdfFileNamer\Lib\FilenameBuilder;
use ReflectionProperty;

/**
 * Extension for PDFExport that customizes PDF filenames.
 *
 * Uses the pipe hook system instead of overriding the class,
 * ensuring compatibility with other PDF plugins like PlantillasPDF.
 */
class PDFExport
{
    /**
     * Hook into the document header rendering to set the custom filename.
     *
     * This pipe is called during insertBusinessDocHeader() with the document model.
     * We use it as an entry point to set the custom filename via reflection,
     * since ExportBase::$fileName is private and setFileName() only works when empty.
     *
     * Returns null to avoid interfering with the pipe's intended purpose.
     */
    protected function qrSubtitleHeader(): Closure
    {
        return function ($model) {
            $this->pdfFileNamerSetFilename($model);
            return null;
        };
    }

    /**
     * Fallback hook in case qrSubtitleHeader does not fire.
     *
     * This pipe is called during insertBusinessDocBody() with the document model.
     */
    protected function qrSubtitleAfterLines(): Closure
    {
        return function ($model) {
            $this->pdfFileNamerSetFilename($model);
            return null;
        };
    }

    /**
     * Sets the custom filename on the PDFExport instance.
     *
     * Registered as an extension method available via $this->pdfFileNamerSetFilename().
     * Uses reflection to overwrite ExportBase::$fileName (private property).
     */
    protected function pdfFileNamerSetFilename(): Closure
    {
        return function ($model) {
            if (!$model instanceof BusinessDocument) {
                return null;
            }

            $docType = $model->modelClassName();
            $settingKey = 'pattern_' . $docType;
            $pattern = Tools::settings('pdffilenamer', $settingKey, '');

            if (empty($pattern)) {
                return null;
            }

            $filename = FilenameBuilder::build($model, $pattern);
            if (empty($filename)) {
                return null;
            }

            // Use reflection to set the private $fileName property in ExportBase.
            // This is necessary because setFileName() only sets the value when empty,
            // and by this point newDoc() has already set it.
            $prop = new ReflectionProperty('FacturaScripts\Core\Lib\Export\ExportBase', 'fileName');
            $prop->setValue($this, $filename);

            return null;
        };
    }
}
