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

use FacturaScripts\Plugins\PdfFileNamer\Lib\Export\PDFExport;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class PDFExportTest extends TestCase
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
            'PDFExport class should exist'
        );
    }

    public function testCorrectNamespace(): void
    {
        $this->assertEquals(
            'FacturaScripts\\Plugins\\PdfFileNamer\\Lib\\Export',
            $this->reflection->getNamespaceName(),
            'PDFExport should be in correct namespace'
        );
    }

    public function testExtendsCoreClassPDFExport(): void
    {
        $this->assertTrue(
            is_subclass_of(PDFExport::class, 'FacturaScripts\\Core\\Lib\\Export\\PDFExport'),
            'PDFExport should extend Core PDFExport'
        );
    }

    // =========================================================================
    // METHOD EXISTENCE TESTS
    // =========================================================================

    public function testShowMethodExists(): void
    {
        $this->assertTrue(
            method_exists(PDFExport::class, 'show'),
            'show method should exist'
        );
    }

    public function testAddBusinessDocPageMethodExists(): void
    {
        $this->assertTrue(
            method_exists(PDFExport::class, 'addBusinessDocPage'),
            'addBusinessDocPage method should exist'
        );
    }

    // =========================================================================
    // METHOD SIGNATURE COMPATIBILITY TESTS
    // =========================================================================

    /**
     * Test that show() method does not have a return type declaration.
     * This ensures compatibility with other core classes that extend
     * the same base class without return type declarations.
     *
     * This test prevents the error:
     * "Declaration of FacturaScripts\Core\Lib\Export\MAILExport::show()
     * must be compatible with FacturaScripts\Plugins\PdfFileNamer\Lib\Export\PDFExport::show(): void"
     */
    public function testShowMethodHasNoReturnType(): void
    {
        $method = $this->reflection->getMethod('show');
        $returnType = $method->getReturnType();

        $this->assertNull(
            $returnType,
            'show() method should not have a return type declaration for compatibility with other export classes'
        );
    }

    /**
     * Test that show() method signature is compatible with parent class.
     * The method should accept a Response parameter by reference.
     */
    public function testShowMethodParameterSignature(): void
    {
        $method = $this->reflection->getMethod('show');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters, 'show() should have exactly one parameter');

        $responseParam = $parameters[0];
        $this->assertEquals('response', $responseParam->getName(), 'Parameter should be named "response"');
        $this->assertTrue($responseParam->isPassedByReference(), 'Parameter should be passed by reference');

        // Check parameter type hint
        $paramType = $responseParam->getType();
        $this->assertNotNull($paramType, 'Parameter should have a type hint');
        $this->assertEquals('FacturaScripts\\Core\\Response', $paramType->getName());
    }

    // =========================================================================
    // METHOD VISIBILITY TESTS
    // =========================================================================

    public function testShowMethodIsPublic(): void
    {
        $method = $this->reflection->getMethod('show');
        $this->assertTrue($method->isPublic(), 'show method should be public');
    }

    public function testAddBusinessDocPageMethodIsPublic(): void
    {
        $method = $this->reflection->getMethod('addBusinessDocPage');
        $this->assertTrue($method->isPublic(), 'addBusinessDocPage method should be public');
    }
}
