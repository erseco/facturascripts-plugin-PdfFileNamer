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

namespace FacturaScripts\Plugins\PdfFileNamer\Extension\Controller;

use Closure;
use FacturaScripts\Core\Model\Base\BusinessDocument;
use FacturaScripts\Core\Tools;
use FacturaScripts\Plugins\PdfFileNamer\Lib\FilenameBuilder;

/**
 * Extension for edit controllers that applies the custom PDF filename
 * after the export engine has generated the response.
 *
 * This provides compatibility with plugins that replace PDFExport and do
 * not support FacturaScripts class extensions, such as PlantillasPDF.
 */
class EditController
{
    /**
     * Rewrites the Content-Disposition header after a PDF export.
     */
    protected function execAfterAction(): Closure
    {
        return function ($action) {
            if ('export' !== $action || 'PDF' !== $this->request->queryOrInput('option', '')) {
                return null;
            }

            $viewName = $this->getMainViewName();
            if (empty($viewName) || !isset($this->views[$viewName])) {
                return null;
            }

            $model = $this->views[$viewName]->model;
            if (!$model instanceof BusinessDocument) {
                return null;
            }

            $settingKey = 'pattern_' . $model->modelClassName();
            $pattern = Tools::settings('pdffilenamer', $settingKey, '');
            if (empty($pattern)) {
                return null;
            }

            $filename = FilenameBuilder::build($model, $pattern);
            if (empty($filename)) {
                return null;
            }

            $this->response->headers->set(
                'Content-Disposition',
                'inline; filename="' . $filename . '.pdf"'
            );

            return null;
        };
    }
}
