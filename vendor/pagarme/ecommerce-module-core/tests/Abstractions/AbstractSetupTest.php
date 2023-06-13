<?php

namespace Pagarme\Core\Test\Abstractions;

use Pagarme\Core\Test\Mock\Concrete\Migrate;
use Pagarme\Core\Test\Mock\Concrete\PlatformCoreSetup;
use PHPUnit\Framework\TestCase;

abstract class AbstractSetupTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        PlatformCoreSetup::bootstrap();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $migrate = new Migrate(PlatformCoreSetup::getDatabaseAccessObject());
        $migrate->down();
    }
}