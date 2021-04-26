<?php

namespace Pagarme\Core\Test\Maintenance\Services\InstallDataSource;

use Pagarme\Core\Maintenance\Services\InstallDataSource\CoreInstallDataSource;
use PHPUnit\Framework\TestCase;

class CoreInstallDataSourceTests extends TestCase
{
    /**
     * @var CoreInstallDataSource
     */
    private $coreInstallDataSource;

    public function setUp()
    {
        $this->coreInstallDataSource = new CoreInstallDataSource();
    }

    public function testGetIntegrityFilePath()
    {
        $this->assertContains(
            DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'integrityData',
            $this->coreInstallDataSource->getIntegrityFilePath()
        );
    }
}
