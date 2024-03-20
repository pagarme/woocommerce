<?php

/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Block\Order\Transaction;

use Pagarme\Core\Payment\Aggregates\Payments\Authentication\AuthenticationStatusEnum;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Order;

defined('ABSPATH') || exit;

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

            $orderId = $this->getData('orderId');
            if ($orderId) {
                $order = new Order($orderId);
                $authorization = $order->get_meta('pagarme_tds_authentication');

                if (!empty($authorization)) {
                    $authorization = json_decode($authorization, true);
                    $value[$this->getLabel('3DS Status')] = __(
                        AuthenticationStatusEnum::statusMessage(
                            $authorization['trans_status']
                        ),
                        'woo-pagarme-payments'
                    );
                }
            }
        } catch (\Exception $e) {
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
     * @return string
     */
    public function convertData($key, $value)
    {
        if ($key === 'amount') {
            return Utils::format_order_price_to_view($value);
        }
        return $value;
    }
}
