<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Model\Config;

use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Factories\ConfigurationFactory;
use Pagarme\Core\Kernel\Repositories\ConfigurationRepository;
use Pagarme\Core\Kernel\ValueObjects\Id\GUID;
use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup as MPSetup;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Data\DataObject;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined('ABSPATH') || exit;

/**
 * Class PagarmeCoreConfigManagement
 * @package Woocommerce\Pagarme\Model\Config
 */
class PagarmeCoreConfigManagement
{
    /** @var ConfigurationRepository */
    private $pagarmeCoreConfigRepository;

    /** @var ConfigurationFactory */
    private $pagarmeCoreConfigFactory;

    /** @var Json */
    private $jsonSerialize;

    /** @var DataObject */
    private $dataObject;

    /**
     * @param ConfigurationFactory|null $pagarmeCoreConfigFactory
     * @param Json|null $jsonSerialize
     */
    public function __construct(
        ConfigurationFactory $pagarmeCoreConfigFactory = null,
        Json $jsonSerialize = null,
        DataObject $dataObject = null
    ) {
        $this->pagarmeCoreConfigFactory = $pagarmeCoreConfigFactory ?? new ConfigurationFactory;
        $this->jsonSerialize = $jsonSerialize ?? new Json;
        $this->dataObject = $dataObject ?? new DataObject;
    }

    public function update(Config $config)
    {
        $data = $config->getData();
        MPSetup::reloadModuleConfigurationData();
        /** @var \Pagarme\Core\Kernel\Aggregates\Configuration */
        $moduleConfig = MPSetup::getModuleConfiguration();
        foreach ($data as $key => $datum) {
            unset($data[$key]);
            $method = $this->getMethod($key, '');
            if (method_exists($this, 'convertKey' . $method)) {
                $method = $this->{'convertKey' . $method}();
            }
            if (method_exists($this, 'convertData' . $method)) {
                $datum = $this->{'convertData' . $method}($datum);
            }
            if (method_exists($moduleConfig, $this->getMethod($method))) {
                $moduleConfig->{$this->getMethod($method)}($datum);
            }
        }
        $this->pagarmeCoreConfigRepository = $this->pagarmeCoreConfigRepository ?? new ConfigurationRepository;
        $this->pagarmeCoreConfigRepository->save($moduleConfig);
    }

    /**
     * @param string $value
     * @param string $type
     * @return string
     */
    private function getMethod(string $value, string $type = 'set')
    {
        return $type . str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
    }

    /**
     * @return string
     */
    public function convertKeyCcAllowSave()
    {
        return 'saveCards';
    }

    /**
     * @return string
     */
    public function convertKeyVoucherCardWallet()
    {
        return 'saveVoucherCards';
    }

    /**
     * @return string
     */
    public function convertKeyAntifraudMinValue()
    {
        return 'antifraudMinAmount';
    }

    /**
     * @return string
     */
    public function convertKeyEnableBillet()
    {
        return 'boletoEnabled';
    }

    /**
     * @return string
     */
    public function convertKeyEnableCreditCard()
    {
        return 'creditCardEnabled';
    }

    /**
     * @return string
     */
    public function convertKeyMultimethods2Cards()
    {
        return 'twoCreditCardsEnabled';
    }

    /**
     * @return string
     */
    public function convertKeyMultimethodsBilletCard()
    {
        return 'boletoCreditCardEnabled';
    }

    /**
     * @param $datum
     * @return bool
     */
    public function convertDataSaveCards($datum)
    {
        return (bool) $datum;
    }

    /**
     * @param $datum
     * @return bool
     */
    public function convertDataAntifraudEnabled($datum)
    {
        return (bool) $datum;
    }

    /**
     * @param $datum
     * @return bool
     */
    public function convertDataSaveVoucherCards($datum)
    {
        return (bool) $datum;
    }

    /**
     * @param $datum
     * @return GUID
     * @throws InvalidParamException
     */
    public function convertDataHubInstallId($datum)
    {
        if (!$datum) {
            $datum = '00000000-0000-0000-0000-000000000000';
        }
        return new GUID($datum);
    }
}
