<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment;

use stdClass;
use WC_Order;
use Woocommerce\Pagarme\Helper\Utils;
use Pagarme\Core\Payment\Aggregates\SavedCard;

defined( 'ABSPATH' ) || exit;

/**
 *  Class Card
 * @package Woocommerce\Pagarme\Model\Payment
 */
class Card extends AbstractPayment
{
    /** @var int */
    protected $payRequestCardNum = 1;

    /**
     * @return SavedCard[]|null
     */
    public function getCards()
    {
        return $this->getCustomer()->get_cards([$this->code]);
    }

    /**
     * @return bool
     */
    public function getIsEnableWallet()
    {
        return (bool) $this->getConfig()->{'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->code)))  . 'Wallet'}();
    }

    /**
     * Return which credit card type will be send. (card ou card_token)
     * @param  array $form_fields
     * @param  array $card_data
     * @param  string $suffix Suffix for each attribute
     * @return array
     */
    protected function handleCardType($form_fields, $card_data, $suffix = '')
    {
        $card_id    = Utils::get_value_by($form_fields, "card_id{$suffix}", false);
        $pagarmetoken = !$suffix ? 'pagarmetoken1' : "pagarmetoken{$suffix}";
        if ($card_id) {
            $card_data['credit_card']['card_id'] = $card_id;
        } else {
            $card_data['credit_card']['card_token'] = Utils::get_value_by($form_fields, $pagarmetoken);
        }
        return $card_data;
    }

    /**
     * @param int $num
     * @return $this
     */
    public function setPayRequestCardNum(int $num)
    {
        $this->payRequestCardNum = $num;
        return $this;
    }

    /**
     * @param WC_Order $wc_order
     * @param array $form_fields
     * @param stdClass|null $customer
     * @return array|string[]
     * @throws \Exception
     */
    public function getPayRequestBase(WC_Order $wc_order, array $form_fields, $customer = null)
    {
        $suffix = $this->payRequestCardNum === 1 ? '' : '2';
        $content = parent::getPayRequestBase($wc_order, $form_fields, $customer);
        $content[$this->getMethodCode()] = [
            'installments' => Utils::get_value_by($form_fields, "installments{$suffix}"),
            'statement_descriptor' => $this->getConfig()->getCcSoftDescriptor(),
            'capture' => $this->getConfig()->getIsActiveCapture(),
            'card' => [
                'billing_address' => $this->getBillingAddressFromCustomer($customer, $wc_order)
            ]
        ];
        $this->charOrderValue = 'card_order_value' . $suffix;
        return $this->handleCardType($form_fields, $content, $suffix);
    }

    protected function getPriceWithInterest($price, $installments, $flag = '')
    {
        $amount = $price;
        $no_interest       = intval($this->getConfig()->getCcInstallmentsWithoutInterest());
        $interest          = Utils::str_to_float($this->getConfig()->getCcInstallmentsInterest());
        $interest_increase = Utils::str_to_float($this->getConfig()->getCcInstallmentsInterestIncrease());
        $max_installments  = intval($this->getConfig()->getCcInstallmentsMaximum());
        if ($settings_by_flag = $this->getConfig()->getCcInstallmentsByFlag()) {
            $no_interest       = intval($settings_by_flag['no_interest'][$flag]);
            $interest          = Utils::str_to_float($settings_by_flag['interest'][$flag]);
            $interest_increase = Utils::str_to_float($settings_by_flag['interest_increase'][$flag]);
            $max_installments  = intval($settings_by_flag['max_installment'][$flag]);
        }
        if ($installments <= $no_interest) {
            return $amount;
        }
        if ($interest) {
            if ($interest_increase && $installments > $no_interest + 1) {
                $interest += ($interest_increase * ($installments - ($no_interest + 1)));
            }
            $amount += Utils::calc_percentage($interest, $price);
        }
        return $amount;
    }
}
