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

namespace FacturaScripts\Test\Plugins\PdfFileNamer;

use Closure;
use FacturaScripts\Plugins\PdfFileNamer\Extension\Lib\Export\PDFExport;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class PDFExportExtensionTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(PDFExport::class);
    }

    // =========================================================================
    // CLASS STRUCTURE TESTS
    // =========================================================================

    public function testClassExists(): void
    {
        $this->assertTrue(
            class_exists(PDFExport::class),
            'PDFExport extension class should exist'
        );
    }

    public function testCorrectNamespace(): void
    {
        $this->assertEquals(
            'FacturaScripts\\Plugins\\PdfFileNamer\\Extension\\Lib\\Export',
            $this->reflection->getNamespaceName(),
            'PDFExport extension should be in the Extension namespace'
        );
    }

    public function testDoesNotExtendCorePDFExport(): void
    {
        $this->assertFalse(
            is_subclass_of(PDFExport::class, 'FacturaScripts\\Core\\Lib\\Export\\PDFExport'),
            'PDFExport extension should NOT extend Core PDFExport (it is an extension, not an override)'
        );
    }

    // =========================================================================
    // METHOD EXISTENCE TESTS
    // =========================================================================

    public function testQrSubtitleHeaderMethodExists(): void
    {
        $this->assertTrue(
            $this->reflection->hasMethod('qrSubtitleHeader'),
            'qrSubtitleHeader method should exist'
        );
    }

    public function testQrSubtitleAfterLinesMethodExists(): void
    {
        $this->assertTrue(
            $this->reflection->hasMethod('qrSubtitleAfterLines'),
            'qrSubtitleAfterLines method should exist'
        );
    }

    public function testPdfFileNamerSetFilenameMethodExists(): void
    {
        $this->assertTrue(
            $this->reflection->hasMethod('pdfFileNamerSetFilename'),
            'pdfFileNamerSetFilename method should exist'
        );
    }

    // =========================================================================
    // METHOD RETURN TYPE TESTS
    // =========================================================================

    public function testQrSubtitleHeaderReturnsClosure(): void
    {
        $extension = new PDFExport();
        $method = $this->reflection->getMethod('qrSubtitleHeader');
        $method->setAccessible(true);

        $result = $method->invoke($extension);

        $this->assertInstanceOf(
            Closure::class,
            $result,
            'qrSubtitleHeader should return a Closure'
        );
    }

    public function testQrSubtitleAfterLinesReturnsClosure(): void
    {
        $extension = new PDFExport();
        $method = $this->reflection->getMethod('qrSubtitleAfterLines');
        $method->setAccessible(true);

        $result = $method->invoke($extension);

        $this->assertInstanceOf(
            Closure::class,
            $result,
            'qrSubtitleAfterLines should return a Closure'
        );
    }

    public function testPdfFileNamerSetFilenameReturnsClosure(): void
    {
        $extension = new PDFExport();
        $method = $this->reflection->getMethod('pdfFileNamerSetFilename');
        $method->setAccessible(true);

        $result = $method->invoke($extension);

        $this->assertInstanceOf(
            Closure::class,
            $result,
            'pdfFileNamerSetFilename should return a Closure'
        );
    }

    // =========================================================================
    // CLOSURE BEHAVIOR TESTS
    // =========================================================================

    public function testClosureReturnsNullForNonBusinessDocument(): void
    {
        $extension = new PDFExport();
        $method = $this->reflection->getMethod('pdfFileNamerSetFilename');
        $method->setAccessible(true);

        $closure = $method->invoke($extension);

        // Pass a non-BusinessDocument object
        $result = $closure->call(new \stdClass(), new \stdClass());

        $this->assertNull(
            $result,
            'Closure should return null for non-BusinessDocument models'
        );
    }

    // =========================================================================
    // EXTENSION PATTERN TESTS
    // =========================================================================

    public function testAllMethodsReturnClosures(): void
    {
        $extension = new PDFExport();
        $methods = $this->reflection->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_PRIVATE
        );

        foreach ($methods as $method) {
            if (strpos($method->name, '__') === 0) {
                continue;
            }

            $method->setAccessible(true);
            $result = $method->invoke($extension);

            $this->assertInstanceOf(
                Closure::class,
                $result,
                sprintf('Method %s should return a Closure', $method->name)
            );
        }
    }

    public function testCanBeInstantiated(): void
    {
        $extension = new PDFExport();
        $this->assertInstanceOf(PDFExport::class, $extension);
    }
}
