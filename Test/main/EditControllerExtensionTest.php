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
use FacturaScripts\Plugins\PdfFileNamer\Extension\Controller\EditController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EditControllerExtensionTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(EditController::class);
    }

    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(EditController::class));
    }

    public function testCorrectNamespace(): void
    {
        $this->assertEquals(
            'FacturaScripts\\Plugins\\PdfFileNamer\\Extension\\Controller',
            $this->reflection->getNamespaceName()
        );
    }

    public function testExecAfterActionReturnsClosure(): void
    {
        $extension = new EditController();
        $method = $this->reflection->getMethod('execAfterAction');
        $method->setAccessible(true);

        $this->assertInstanceOf(Closure::class, $method->invoke($extension));
    }
}
