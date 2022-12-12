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

defined( 'ABSPATH' ) || exit;

/**
 * Class Config
 * @package Woocommerce\Pagarme\Model\Data
 */
class Config extends DataObject
{
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
        }
        $this->save();
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
}
