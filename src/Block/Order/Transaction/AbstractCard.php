<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Order\Transaction;

use Woocommerce\Pagarme\Helper\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Class AbstractCard
 * @package Woocommerce\Pagarme\Block\Order
 */
abstract class AbstractCard extends AbstractTransaction
{
    /**
     * @var string
     */
    protected $_template = 'templates/order/transaction/card';

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
            if ($this->getTransaction() && $this->getTransaction()->getPostData()) {
                $postData = $this->getTransaction()->getPostData();
                if (property_exists($postData, 'tran_data')) {
                    $data = $this->jsonSerialize->unserialize($postData->tran_data);
                    foreach ($data as $key => $datum) {
                        if ($key === 'card' && is_array($datum)) {
                            foreach ($datum as $key2 => $datum2) {
                                if (in_array($key2, $this->cardData)) {
                                    $value[$this->getLabel($key2)] = $this->convertData($key2, $datum2);
                                }
                            }
                            continue;
                        }
                        if (in_array($key, $this->cardData)) {
                            $value[$this->getLabel($key)] =  $this->convertData($key, $datum);
                        }
                    }
                }
            }
        } catch (\Exception $e) {}
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
