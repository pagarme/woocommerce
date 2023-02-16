<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\Data;

use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class Billet
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
class Billet extends AbstractPayment
{
    /** @var Multicustomers|null */
    private $multicustomers;

    public function __construct(
        Json $jsonSerialize = null,
        array $data = [],
        Multicustomers $multicustomers = null
    ) {
        parent::__construct($jsonSerialize, $data);
        if (!$multicustomers) {
            $multicustomers = new Multicustomers;
        }
        $this->multicustomers = $multicustomers;
        $this->init();
    }

    /**
     * @return void
     */
    private function init()
    {
        if ($this->getPostPaymentContent() && is_array($this->getPostPaymentContent()) && array_key_exists('billet', $this->getPostPaymentContent())) {
            foreach ($this->getPostPaymentContent()['billet'] as $field => $value) {
                $this->{$this->getMethod($field)}($value);
            }
        }
    }

    /**
     * @param $data
     * @return Billet
     */
    public function setMulticustomers($data)
    {
        return $this->setData('multicustomers', $this->multicustomers->setData($data));
    }
}
