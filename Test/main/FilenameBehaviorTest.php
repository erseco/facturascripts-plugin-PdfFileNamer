<?php

/**
 * This file is part of PdfFileNamer plugin for FacturaScripts.
 * Copyright (C) 2026 Ernesto Serrano <info@ernesto.es>
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

declare(strict_types=1);

namespace FacturaScripts\Test\Plugins\PdfFileNamer;

use FacturaScripts\Core\Lib\Export\ExportBase;
use FacturaScripts\Core\Lib\Export\PDFExport;
use FacturaScripts\Core\Model\Cliente;
use FacturaScripts\Core\Model\Empresa;
use FacturaScripts\Core\Model\FacturaCliente;
use FacturaScripts\Core\Model\Proveedor;
use FacturaScripts\Core\Request;
use FacturaScripts\Core\Response;
use FacturaScripts\Core\Tools;
use FacturaScripts\Plugins\PdfFileNamer\Extension\Controller\EditController;
use FacturaScripts\Plugins\PdfFileNamer\Extension\Lib\Export\PDFExport as PDFExtension;
use FacturaScripts\Plugins\PdfFileNamer\Init;
use FacturaScripts\Plugins\PdfFileNamer\Lib\FilenameBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use stdClass;

class FilenameBehaviorTest extends TestCase
{
    protected function tearDown(): void
    {
        Tools::settingsClear();
    }

    public function testBuildExtractsDocumentCompanyAndSubjectTokens(): void
    {
        $customer = new Cliente();
        $customer->nombre = 'Customer';
        $customer->razonsocial = 'Customer Ltd';
        $customer->cifnif = 'CUSTOMER-ID';
        $invoice = $this->document($customer);
        $company = (new Empresa())->all([], [], 0, 1)[0];
        $invoice->idempresa = $company->idempresa;
        $tokens = FilenameBuilder::extractTokens($invoice);
        $this->assertSame('Customer Ltd', $tokens['customer']);
        $this->assertSame('CUSTOMER-ID', $tokens['customer_cif']);
        $this->assertSame((string)$company->nombre, $tokens['company_name']);
        $this->assertSame((string)($company->nombrecorto ?? $company->nombre), $tokens['company']);
        $this->assertSame((string)$company->cifnif, $tokens['company_cif']);
        $this->assertSame('2026-01-15_FAC_001_Customer Ltd', FilenameBuilder::build(
            $invoice,
            '{year}-{month}-{day}_{code}_{customer}'
        ));
        $this->assertSame('', FilenameBuilder::build($invoice, ''));

        $supplier = new Proveedor();
        $supplier->razonsocial = 'Supplier Ltd';
        $supplier->cifnif = 'SUPPLIER-ID';
        $tokens = FilenameBuilder::extractTokens($this->document($supplier));
        $this->assertSame('Supplier Ltd', $tokens['supplier']);
        $this->assertSame('SUPPLIER-ID', $tokens['supplier_cif']);
        $this->assertSame('', $tokens['customer']);

        $invoice = $this->document();
        $invoice->fecha = 'invalid';
        $invoice->idempresa = -1;
        $tokens = FilenameBuilder::extractTokens($invoice);
        foreach (['year', 'month', 'day', 'company', 'customer', 'supplier'] as $key) {
            $this->assertSame('', $tokens[$key]);
        }
    }

    public function testNativePdfHooksOverwriteExistingFilenameAndPreserveFallbacks(): void
    {
        PDFExport::addExtension(new PDFExtension());
        $pdf = new PDFExport();
        $filename = new ReflectionProperty(ExportBase::class, 'fileName');
        $filename->setAccessible(true);
        $filename->setValue($pdf, 'original');
        $invoice = $this->document();
        foreach (['qrSubtitleHeader', 'qrSubtitleAfterLines'] as $hook) {
            $patterns = ['' => 'original', '   ' => 'original', 'Invoice {code}' => 'Invoice FAC_001'];
            foreach ($patterns as $pattern => $expected) {
                $filename->setValue($pdf, 'original');
                Tools::settingsSet('pdffilenamer', 'pattern_FacturaCliente', $pattern);
                $this->assertNull($pdf->pipe($hook, $invoice));
                $this->assertSame($expected, $filename->getValue($pdf));
            }
        }
        $this->assertNull($pdf->pdfFileNamerSetFilename(new stdClass()));
        $this->assertSame('Invoice FAC_001', $filename->getValue($pdf));
    }

    public function testControllerFallbackOnlyRenamesPdfBusinessDocuments(): void
    {
        $controller = new class () {
            public Request $request;
            public Response $response;
            public array $views = [];
            public string $mainView = 'invoice';

            public function getMainViewName(): string
            {
                return $this->mainView;
            }
        };
        $controller->response = new Response();
        $method = new ReflectionMethod(EditController::class, 'execAfterAction');
        $method->setAccessible(true);
        $hook = $method->invoke(new EditController())->bindTo($controller, get_class($controller));
        $invoice = $this->document();
        foreach (['query', 'request'] as $source) {
            $controller->request = new Request([$source => ['option' => 'PDF']]);
            $controller->views = ['invoice' => (object)['model' => $invoice]];
            Tools::settingsSet('pdffilenamer', 'pattern_FacturaCliente', 'Invoice {code}');
            $this->assertNull($hook('export'));
            $this->assertSame(
                'inline; filename="Invoice FAC_001.pdf"',
                $controller->response->headers->get('Content-Disposition')
            );
        }
        $cases = ['action', 'non-pdf', 'query-precedence', 'empty-view', 'missing-view',
            'non-document', 'empty-pattern', 'blank-filename'];
        foreach ($cases as $case) {
            $controller->response->headers->set('Content-Disposition', 'original');
            $controller->request = new Request(['query' => ['option' => 'PDF']]);
            $controller->mainView = 'invoice';
            $controller->views = ['invoice' => (object)['model' => $invoice]];
            Tools::settingsSet('pdffilenamer', 'pattern_FacturaCliente', 'Invoice {code}');
            if ($case === 'non-pdf' || $case === 'query-precedence') {
                $controller->request = new Request(['query' => ['option' => 'CSV'], 'request' => ['option' => 'PDF']]);
            } elseif ($case === 'empty-view') {
                $controller->mainView = '';
            } elseif ($case === 'missing-view') {
                $controller->views = [];
            } elseif ($case === 'non-document') {
                $controller->views['invoice']->model = new stdClass();
            } elseif ($case === 'empty-pattern' || $case === 'blank-filename') {
                Tools::settingsSet('pdffilenamer', 'pattern_FacturaCliente', $case === 'empty-pattern' ? '' : '  ');
            }
            $this->assertNull($hook($case === 'action' ? 'save' : 'export'));
            $this->assertSame('original', $controller->response->headers->get('Content-Disposition'), $case);
        }
    }

    public function testLifecycleRegistersExtensionsAndPreservesConfiguredPatterns(): void
    {
        $pattern = Tools::settings('pdffilenamer', 'pattern_FacturaCliente', '');
        $init = new Init();
        $init->init();
        $init->update();
        $init->uninstall();
        Tools::settingsClear();
        $this->assertSame($pattern, Tools::settings('pdffilenamer', 'pattern_FacturaCliente'));
        $this->assertTrue((new PDFExport())->hasExtension('pdfFileNamerSetFilename'));
    }

    private function document($subject = null): FacturaCliente
    {
        $invoice = $this->getMockBuilder(FacturaCliente::class)
            ->onlyMethods(['getSubject', 'modelClassName'])->getMock();
        $invoice->method('getSubject')->willReturn($subject);
        $invoice->method('modelClassName')->willReturn('FacturaCliente');
        $invoice->codigo = 'FAC/001';
        $invoice->numero = '1';
        $invoice->codserie = 'A';
        $invoice->fecha = '15-01-2026';
        $invoice->idempresa = null;
        return $invoice;
    }
}
