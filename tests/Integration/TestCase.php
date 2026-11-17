<?php

namespace Tests\Integration;

use LogicException;
use Tests\TestCase as BaseTestCase;

/**
 * Base de las pruebas contra PostgreSQL. Solo corren con phpunit.integration.xml
 * y únicamente sobre la base aislada sistema_tutorias_uagrm_test.
 *
 * La verificación se hace en setUpTraits(), ANTES de que RefreshDatabase
 * ejecute migrate:fresh, para que nunca se pueda vaciar la base operativa.
 */
abstract class TestCase extends BaseTestCase
{
    private const TEST_DATABASE = 'sistema_tutorias_uagrm_test';

    private const OPERATIONAL_DATABASE = 'sistema_tutorias_uagrm';

    protected function setUpTraits()
    {
        if ((string) env('INTEGRATION_TESTS', '0') !== '1') {
            $this->markTestSkipped('Run integration tests with phpunit.integration.xml.');
        }

        $this->assertIsolatedDatabase();

        return parent::setUpTraits();
    }

    private function assertIsolatedDatabase(): void
    {
        $connectionName = config('database.default');
        $connection = config("database.connections.{$connectionName}", []);
        $database = $connection['database'] ?? null;
        $url = $connection['url'] ?? null;

        if ($database === self::OPERATIONAL_DATABASE) {
            throw new LogicException('Integration tests are forbidden from using the operational database.');
        }

        if (
            $connectionName !== 'pgsql'
            || $database !== self::TEST_DATABASE
            || ($url !== null && $url !== '')
        ) {
            throw new LogicException('Integration tests must use the isolated PostgreSQL test database.');
        }
    }
}
