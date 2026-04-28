<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Em ambiente de testes com SQLite, o stancl/tenancy deixa arquivos residuais 
        // dos tenants (ex: tenant_xxx.sqlite) que conflitam com RefreshDatabase.
        // Limpamos eles antes de cada teste para grantir o isolamento e evitar 'Database already exists'.
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("DROP DATABASE IF EXISTS `tenantcooperativa-teste`");
            \Illuminate\Support\Facades\DB::statement("DROP DATABASE IF EXISTS `tenantstfsaude`");
            \Illuminate\Support\Facades\DB::statement("DROP DATABASE IF EXISTS `tenantintegracao-teste`");
            \Illuminate\Support\Facades\DB::statement("DROP DATABASE IF EXISTS `tenantadesao-teste`");
            \Illuminate\Support\Facades\DB::statement("DROP DATABASE IF EXISTS `tenantfinanceiro-teste`");
        }
    }
}
