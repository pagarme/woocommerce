<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Config;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Factories\ConfigurationFactory;
use Pagarme\Core\Kernel\Repositories\ConfigurationRepository;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Data\DataObject;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

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

    /** @var \Pagarme\Core\Kernel\Aggregates\Configuration */
    private $moduleConfig;

    /**
     * @param ConfigurationFactory|null $pagarmeCoreConfigFactory
     * @param ConfigurationRepository|null $pagarmeCoreConfigRepository
     * @param Json|null $jsonSerialize
     */
    public function __construct(
        ConfigurationFactory $pagarmeCoreConfigFactory = null,
        ConfigurationRepository $pagarmeCoreConfigRepository = null,
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
        foreach ($data as $key => $datum) {
            unset($data[$key]);
            $key = $method = $this->getMethod($key, '');
            if (method_exists($this, 'convertKey' . $method)) {
                $key = $this->{'convertKey' . $method}();
            }
            $data[lcfirst($key)] = $datum;
        }
        $this->setCardConfigs($data);
        $data = $this->pagarmeCoreConfigFactory->createFromJsonData(
            $this->jsonSerialize->serialize($data)
        );
        $this->pagarmeCoreConfigRepository = $this->pagarmeCoreConfigRepository ?? new ConfigurationRepository;
        $this->pagarmeCoreConfigRepository->save($data);
    }

    public function setCardConfigs(&$data)
    {
        $data['cardConfigs'] = [];
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
    public function convertKeyEnableCreditCard()
    {
        return 'saveVoucherCards';
    }
}
