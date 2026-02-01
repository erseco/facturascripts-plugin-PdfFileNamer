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

namespace FacturaScripts\Plugins\PdfFileNamer\Lib;

use FacturaScripts\Core\Model\Base\BusinessDocument;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\Empresa;

/**
 * Service class for building PDF filenames from patterns.
 */
class FilenameBuilder
{
    private const MAX_LENGTH = 200;

    /**
     * Builds a filename from a pattern and a business document.
     */
    public static function build(BusinessDocument $model, string $pattern): string
    {
        if (empty($pattern)) {
            return '';
        }

        $tokens = self::extractTokens($model);
        $filename = self::replaceTokens($pattern, $tokens);

        return self::sanitize($filename);
    }

    /**
     * Extracts all available tokens from a business document.
     *
     * @return array<string, string>
     */
    public static function extractTokens(BusinessDocument $model): array
    {
        $tokens = [
            'code' => (string)($model->codigo ?? ''),
            'number' => (string)($model->numero ?? ''),
            'serie' => (string)($model->codserie ?? ''),
            'date' => (string)($model->fecha ?? ''),
            'year' => '',
            'month' => '',
            'day' => '',
            'company' => '',
            'company_name' => '',
            'company_cif' => '',
            'customer' => '',
            'customer_cif' => '',
            'supplier' => '',
            'supplier_cif' => '',
            'doctype' => $model->modelClassName(),
        ];

        // Extract date components
        if (!empty($model->fecha)) {
            $date = strtotime($model->fecha);
            if ($date !== false) {
                $tokens['year'] = date('Y', $date);
                $tokens['month'] = date('m', $date);
                $tokens['day'] = date('d', $date);
            }
        }

        // Extract company data
        if (!empty($model->idempresa)) {
            $empresa = new Empresa();
            if ($empresa->loadFromCode($model->idempresa)) {
                $tokens['company'] = (string)($empresa->nombrecorto ?? $empresa->nombre ?? '');
                $tokens['company_name'] = (string)($empresa->nombre ?? '');
                $tokens['company_cif'] = (string)($empresa->cifnif ?? '');
            }
        }

        // Extract customer/supplier data based on document type
        $subject = $model->getSubject();
        if ($subject !== null) {
            $subjectClass = $subject->modelClassName();

            if ($subjectClass === 'Cliente') {
                $tokens['customer'] = (string)($subject->razonsocial ?? $subject->nombre ?? '');
                $tokens['customer_cif'] = (string)($subject->cifnif ?? '');
            } elseif ($subjectClass === 'Proveedor') {
                $tokens['supplier'] = (string)($subject->razonsocial ?? $subject->nombre ?? '');
                $tokens['supplier_cif'] = (string)($subject->cifnif ?? '');
            }
        }

        return $tokens;
    }

    /**
     * Replaces tokens in a pattern with their values.
     *
     * @param array<string, string> $tokens
     */
    public static function replaceTokens(string $pattern, array $tokens): string
    {
        $result = $pattern;

        foreach ($tokens as $key => $value) {
            $result = str_replace('{' . $key . '}', $value, $result);
        }

        return $result;
    }

    /**
     * Sanitizes a filename to be safe for filesystems.
     * Preserves spaces and user-defined separators.
     */
    public static function sanitize(string $filename): string
    {
        // Convert to ASCII
        $filename = Tools::ascii($filename);

        // Replace only unsafe characters for filesystems
        $unsafe = ['/', '\\', ':', '*', '?', '"', '<', '>', '|'];
        $filename = str_replace($unsafe, '_', $filename);

        // Trim spaces from start and end only
        $filename = trim($filename);

        // Limit length
        if (strlen($filename) > self::MAX_LENGTH) {
            $filename = substr($filename, 0, self::MAX_LENGTH);
            $filename = rtrim($filename);
        }

        return $filename;
    }
}
