<?php

declare(strict_types=1);

/**
 * This file is part of PdfFileNamer plugin for FacturaScripts.
 * Copyright (C) 2026 Ernesto Serrano <info@ernesto.es>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 */

namespace FacturaScripts\Plugins\PdfFileNamer\Lib\Export;

use FacturaScripts\Core\Lib\Export\PDFExport as CorePDFExport;
use FacturaScripts\Core\Model\Base\BusinessDocument;
use FacturaScripts\Core\Response;
use FacturaScripts\Core\Tools;
use FacturaScripts\Plugins\PdfFileNamer\Lib\FilenameBuilder;

/**
 * Extended PDFExport that allows custom PDF filenames.
 */
class PDFExport extends CorePDFExport
{
    /** @var string|null Custom filename for the PDF */
    private ?string $customFileName = null;

    /**
     * Adds a new page with the document data.
     * Stores the model for custom filename generation.
     *
     * @param BusinessDocument $model
     *
     * @return bool
     */
    public function addBusinessDocPage($model): bool
    {
        // Generate custom filename if pattern is configured
        if ($model instanceof BusinessDocument) {
            $docType = $model->modelClassName();
            $settingKey = 'pattern_' . $docType;
            $pattern = Tools::settings('pdffilenamer', $settingKey, '');

            if (!empty($pattern)) {
                $filename = FilenameBuilder::build($model, $pattern);
                if (!empty($filename)) {
                    $this->customFileName = $filename;
                }
            }
        }

        // Call parent method to generate the PDF page
        return parent::addBusinessDocPage($model);
    }

    /**
     * Shows the PDF in the response.
     * Overrides the Content-Disposition header with custom filename if set.
     *
     * @param Response $response
     */
    public function show(Response &$response): void
    {
        // If we have a custom filename, set it in the header
        if (!empty($this->customFileName)) {
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set(
                'Content-Disposition',
                'inline; filename="' . $this->customFileName . '.pdf"'
            );
            $response->setContent($this->getDoc());
            return;
        }

        // Otherwise use parent implementation
        parent::show($response);
    }
}
