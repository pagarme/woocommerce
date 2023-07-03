<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Block\Order\Email;

use Woocommerce\Pagarme\Helper\Utils;

defined('ABSPATH') || exit;

/**
 * Class AbstractCard
 * @package Woocommerce\Pagarme\Block\Order
 */
abstract class AbstractCard extends AbstractEmail
{
    /**
     * @var string
     */
    protected $_template = 'templates/order/email/card';

    protected $cardData = [
        'holder_name',
        'brand',
        'last_four_digits',
        'installments',
        'amount'
    ];

    /**
     * @return array
     */
    public function getCardDetails()
    {
        $value = [];
        try {
            if (!$this->getTransaction() && !$this->getTransaction()->getPostData()) {
                return [];
            }

            $postData = $this->getTransaction()->getPostData();
            if (!property_exists($postData, 'tran_data')) {
                return [];
            }

            $tranData = $this->jsonSerialize->unserialize($postData->tran_data);
            $tranDataCard = $tranData['card'];

            foreach ($tranDataCard as $key => $data) {
                if (is_array($tranData) && in_array($key, $this->cardData)) {
                    $value[$this->getLabel($key)] = $this->convertData($key, $data);
                }
            }

            foreach ($tranData as $key => $data) {
                if (is_array($tranDataCard) && in_array($key, $this->cardData)) {
                    $value[$this->getLabel($key)] =  $this->convertData($key, $data);
                }
            }
        } catch (\Exception $e) {
            // @todo
        }

        return $value;
    }

    /**
     * @param string $key
     * @return string|null
     */
    private function getLabel(string $key)
    {
        return __(ucwords(str_replace('_', ' ', $key)), 'woo-pagarme-payments');
    }

    /**
     * @param $key
     * @param $value
     * @return String
     */
    public function convertData($key, $value)
    {
        if ($key === 'amount') {
            return Utils::format_order_price_to_view($value);
        }
        return $value;
    }
}
