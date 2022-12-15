<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model;

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Data\DataObject;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;
use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup as CoreSetup;

defined( 'ABSPATH' ) || exit;

/**
 * Class Config
 * @package Woocommerce\Pagarme\Model\Data
 */
class Config extends DataObject
{
    /** @var string */
    const HUB_SANDBOX_ENVIRONMENT = 'Sandbox';

    /**
     * @param Json|null $jsonSerialize
     * @param array $data
     */
    public function __construct(
        Json $jsonSerialize = null,
        array $data = []
    ) {
        parent::__construct($jsonSerialize, $data);
        $this->init();
    }

    /**
     * @return void
     */
    private function init()
    {
        foreach ($this->getOptions() as $key => $value) {
            $this->setData($key, $value);
        }
        add_action(
            'update_option_' . $this->getOptionKey(),
            [ $this, 'updateOption' ],
            10, 3
        );
    }

    /**
     * @return void
     */
    public function updateOption()
    {
        if (array_key_exists($this->getOptionKey(), $_POST)) {
            $values = $_POST[$this->getOptionKey()];
            if ($values && is_array($values)) {
                foreach ($values as $key => $value) {
                    $this->setData($key, sanitize_text_field($value));
                }
            }
            $this->save();
        }
    }

    /**
     * @param Config|null $config
     * @return void
     */
    public function save(Config $config = null)
    {
        if (!$config) {
            $config = $this;
        }
        update_option($this->getOptionKey(), $config->getData());
    }

    /**
     * @return false|mixed|null
     */
    private function getOptions()
    {
        return get_option($this->getOptionKey());
    }

    /**
     * @return string
     */
    public function getOptionKey()
    {
        return Core::tag_name('settings');
    }

    /**
     * @return mixed
     */
    private function getHubAppId()
    {
        return CoreSetup::getHubAppPublicAppKey();
    }

    /**
     * @return bool
     */
    public function getIsSandboxMode()
    {
        return ( $this->getHubEnvironment() === static::HUB_SANDBOX_ENVIRONMENT ||
            strpos(($this->getProductionSecretKey()) ?? '', 'sk_test') !== false ||
            strpos(($this->getProductionPublicKey()) ?? '', 'pk_test') !== false
        );
    }

    /**
     * @return string
     */
    public function getHubUrl(): string
    {
        return ($this->getHubInstallId()) ? $this->getHubViewIntegrationUrl() : $this->getHubIntegrateUrl();
    }

    /**
     * @return string
     */
    private function getHubIntegrateUrl(): string
    {
        return $this->getHubBaseUrl() . $this->getHubParamsUrl();
    }

    /**
     * @return string
     */
    private function getHubBaseUrl()
    {
        return sprintf(
            'https://hub.pagar.me/apps/%s/authorize',
            $this->getHubAppId()
        );
    }

    /**
     * @return string
     */
    private function getHubParamsUrl()
    {
        return sprintf(
            '?redirect=%s?install_token=%s',
            Core::get_hub_url(),
            $this->getHubInstallToken()
        );
    }

    /**
     * @return string
     */
    private function getHubViewIntegrationUrl()
    {
        return sprintf(
            'https://hub.pagar.me/apps/%s/edit/%s',
            $this->getHubAppId(),
            $this->getHubInstallId()
        );
    }
}
