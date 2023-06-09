<?php

namespace Pagarme\Core\Test\Kernel\Aggregates\Factories\Configurations;


use Pagarme\Core\Kernel\Factories\Configurations\PixConfigFactory;
use PHPUnit\Framework\TestCase;

class PixConfigFactoryTest extends TestCase
{
    /**
     * @var object
     */
    private $dataWithAdditionalInformation;
    /**
     * @var PixConfigFactory
     */
    private $pixConfigFactory;

    public function setUp(): void
    {
        $this->dataWithAdditionalInformation = (object) array(
            'enabled' => true,
            'expirationQrCode' => 300,
            'bankType' => 'Pagar.me',
            'additionalInformation' => array(
                array(
                    'name' => 'information name',
                    'value' => 'information value'
                )
            )
        );

        $this->pixConfigFactory = new PixConfigFactory();
    }

    public function testCreatePixConfigurationWithAdditionalConfiguration()
    {
        $pixConfig = $this->pixConfigFactory
            ->createFromDbData($this->dataWithAdditionalInformation);

        $this->assertEquals(
            $this->dataWithAdditionalInformation->additionalInformation,
            $pixConfig->getAdditionalInformation()
        );

        $serializedPixConfig = $pixConfig->jsonSerialize();

        $this->assertEquals(
            (array) $this->dataWithAdditionalInformation->additionalInformation,
            $serializedPixConfig["additionalInformation"]
        );
    }

    public function testCreatePixConfigurationWithoutAdditionalConfiguration()
    {
        $dataWithoutAdditionalInformation =
            clone $this->dataWithAdditionalInformation;

        unset($dataWithoutAdditionalInformation->additionalInformation);

        $pixConfig = $this->pixConfigFactory
            ->createFromDbData($dataWithoutAdditionalInformation);

        $this->assertEquals(
            null,
            $pixConfig->getAdditionalInformation()
        );

        $serializedPixConfig = $pixConfig->jsonSerialize();

        $this->assertEquals(
            null,
            $serializedPixConfig["additionalInformation"]
        );
    }
}
