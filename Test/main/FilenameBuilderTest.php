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

namespace FacturaScripts\Test\Plugins\PdfFileNamer;

use FacturaScripts\Plugins\PdfFileNamer\Lib\FilenameBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FilenameBuilderTest extends TestCase
{
    // =========================================================================
    // CLASS STRUCTURE TESTS
    // =========================================================================

    public function testClassExists(): void
    {
        $this->assertTrue(
            class_exists(FilenameBuilder::class),
            'FilenameBuilder class should exist'
        );
    }

    public function testCorrectNamespace(): void
    {
        $reflection = new ReflectionClass(FilenameBuilder::class);
        $this->assertEquals(
            'FacturaScripts\\Plugins\\PdfFileNamer\\Lib',
            $reflection->getNamespaceName(),
            'FilenameBuilder should be in correct namespace'
        );
    }

    // =========================================================================
    // SANITIZE TESTS
    // =========================================================================

    public function testSanitizeRemovesUnsafeCharacters(): void
    {
        $input = 'file/with\\unsafe:chars*and?quotes"and<brackets>and|pipes';
        $result = FilenameBuilder::sanitize($input);

        $this->assertStringNotContainsString('/', $result);
        $this->assertStringNotContainsString('\\', $result);
        $this->assertStringNotContainsString(':', $result);
        $this->assertStringNotContainsString('*', $result);
        $this->assertStringNotContainsString('?', $result);
        $this->assertStringNotContainsString('"', $result);
        $this->assertStringNotContainsString('<', $result);
        $this->assertStringNotContainsString('>', $result);
        $this->assertStringNotContainsString('|', $result);
    }

    public function testSanitizePreservesSpaces(): void
    {
        $input = 'file with spaces';
        $result = FilenameBuilder::sanitize($input);

        $this->assertEquals('file with spaces', $result);
    }

    public function testSanitizePreservesMultipleSpaces(): void
    {
        $input = 'file   with   multiple   spaces';
        $result = FilenameBuilder::sanitize($input);

        $this->assertEquals('file   with   multiple   spaces', $result);
    }

    public function testSanitizePreservesUnderscores(): void
    {
        $input = 'file_with_underscores';
        $result = FilenameBuilder::sanitize($input);

        $this->assertEquals('file_with_underscores', $result);
    }

    public function testSanitizeTrimsLeadingAndTrailingSpaces(): void
    {
        $input = '  file with leading and trailing  ';
        $result = FilenameBuilder::sanitize($input);

        $this->assertStringStartsNotWith(' ', $result);
        $this->assertStringEndsNotWith(' ', $result);
        $this->assertEquals('file with leading and trailing', $result);
    }

    public function testSanitizeLimitsLength(): void
    {
        $input = str_repeat('a', 300);
        $result = FilenameBuilder::sanitize($input);

        $this->assertLessThanOrEqual(200, strlen($result));
    }

    public function testSanitizeTrimsAfterLengthLimit(): void
    {
        // Create a string that will have trailing spaces after truncation
        $input = str_repeat('a', 198) . '  bb';
        $result = FilenameBuilder::sanitize($input);

        $this->assertLessThanOrEqual(200, strlen($result));
        $this->assertStringEndsNotWith(' ', $result);
    }

    public function testSanitizeConvertsAccentedCharacters(): void
    {
        $input = 'factura_cafe_espanol';
        $result = FilenameBuilder::sanitize($input);

        $this->assertStringNotContainsString("\xc3", $result);
    }

    public function testSanitizeHandlesEmptyString(): void
    {
        $input = '';
        $result = FilenameBuilder::sanitize($input);

        $this->assertEquals('', $result);
    }

    public function testSanitizeHandlesOnlyUnsafeCharacters(): void
    {
        $input = '///\\\\:::***???"""<<<>>>|||';
        $result = FilenameBuilder::sanitize($input);

        // All replaced with underscores
        $this->assertMatchesRegularExpression('/^_+$/', $result);
    }

    public function testSanitizeHandlesOnlySpaces(): void
    {
        $input = '     ';
        $result = FilenameBuilder::sanitize($input);

        $this->assertEquals('', $result);
    }

    // =========================================================================
    // REPLACE TOKENS TESTS
    // =========================================================================

    public function testReplaceTokensReplacesSimpleToken(): void
    {
        $pattern = 'invoice_{code}';
        $tokens = ['code' => 'FAC001'];
        $result = FilenameBuilder::replaceTokens($pattern, $tokens);

        $this->assertEquals('invoice_FAC001', $result);
    }

    public function testReplaceTokensReplacesMultipleTokens(): void
    {
        $pattern = '{company}_{code}_{customer}';
        $tokens = [
            'company' => 'MiEmpresa',
            'code' => 'FAC001',
            'customer' => 'ClienteX',
        ];
        $result = FilenameBuilder::replaceTokens($pattern, $tokens);

        $this->assertEquals('MiEmpresa_FAC001_ClienteX', $result);
    }

    public function testReplaceTokensPreservesUnknownTokens(): void
    {
        $pattern = '{code}_{unknown}';
        $tokens = ['code' => 'FAC001'];
        $result = FilenameBuilder::replaceTokens($pattern, $tokens);

        $this->assertEquals('FAC001_{unknown}', $result);
    }

    public function testReplaceTokensHandlesEmptyPattern(): void
    {
        $pattern = '';
        $tokens = ['code' => 'FAC001'];
        $result = FilenameBuilder::replaceTokens($pattern, $tokens);

        $this->assertEquals('', $result);
    }

    public function testReplaceTokensHandlesEmptyTokens(): void
    {
        $pattern = '{code}_{number}';
        $tokens = [];
        $result = FilenameBuilder::replaceTokens($pattern, $tokens);

        $this->assertEquals('{code}_{number}', $result);
    }

    public function testReplaceTokensHandlesEmptyTokenValue(): void
    {
        $pattern = '{company}_{code}';
        $tokens = [
            'company' => '',
            'code' => 'FAC001',
        ];
        $result = FilenameBuilder::replaceTokens($pattern, $tokens);

        $this->assertEquals('_FAC001', $result);
    }

    public function testReplaceTokensWithSpacesInPattern(): void
    {
        $pattern = '{company} {code} {customer}';
        $tokens = [
            'company' => 'MiEmpresa',
            'code' => 'FAC001',
            'customer' => 'ClienteX',
        ];
        $result = FilenameBuilder::replaceTokens($pattern, $tokens);

        $this->assertEquals('MiEmpresa FAC001 ClienteX', $result);
    }

    public function testReplaceTokensWithCompanyNameToken(): void
    {
        $pattern = '{company_name}_{code}';
        $tokens = [
            'company_name' => 'Mi Empresa S.L.',
            'code' => 'FAC001',
        ];
        $result = FilenameBuilder::replaceTokens($pattern, $tokens);

        $this->assertEquals('Mi Empresa S.L._FAC001', $result);
    }

    // =========================================================================
    // INTEGRATION TESTS
    // =========================================================================

    public function testPatternWithDateTokens(): void
    {
        $pattern = '{year}-{month}-{day}_{code}';
        $tokens = [
            'year' => '2026',
            'month' => '01',
            'day' => '15',
            'code' => 'FAC001',
        ];
        $result = FilenameBuilder::replaceTokens($pattern, $tokens);

        $this->assertEquals('2026-01-15_FAC001', $result);
    }

    public function testSanitizeAfterReplaceTokensPreservesSpaces(): void
    {
        $pattern = '{company} {code}';
        $tokens = [
            'company' => 'Mi Empresa S.L.',
            'code' => 'FAC001',
        ];

        $replaced = FilenameBuilder::replaceTokens($pattern, $tokens);
        $result = FilenameBuilder::sanitize($replaced);

        $this->assertEquals('Mi Empresa S.L. FAC001', $result);
        $this->assertStringContainsString(' ', $result);
    }

    public function testSanitizeAfterReplaceTokensRemovesUnsafe(): void
    {
        $pattern = '{company}/{code}';
        $tokens = [
            'company' => 'Mi Empresa S.L.',
            'code' => 'FAC/001',
        ];

        $replaced = FilenameBuilder::replaceTokens($pattern, $tokens);
        $result = FilenameBuilder::sanitize($replaced);

        $this->assertStringNotContainsString('/', $result);
        $this->assertStringContainsString(' ', $result);
    }

    public function testFullPatternWithAllTokenTypes(): void
    {
        $pattern = '{day}{company} {code} {customer}';
        $tokens = [
            'day' => '01',
            'company' => 'E-1336',
            'code' => 'FAC2026A2',
            'customer' => 'cliente1',
        ];

        $replaced = FilenameBuilder::replaceTokens($pattern, $tokens);
        $result = FilenameBuilder::sanitize($replaced);

        $this->assertEquals('01E-1336 FAC2026A2 cliente1', $result);
    }

    public function testCompanyNameVsCompanyToken(): void
    {
        $pattern = '{company} vs {company_name}';
        $tokens = [
            'company' => 'MiEmpresa',
            'company_name' => 'Mi Empresa Sociedad Limitada',
        ];

        $replaced = FilenameBuilder::replaceTokens($pattern, $tokens);
        $result = FilenameBuilder::sanitize($replaced);

        $this->assertEquals('MiEmpresa vs Mi Empresa Sociedad Limitada', $result);
    }
}
