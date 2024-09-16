<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Checkout\Form;

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Subscription;
use Woocommerce\Pagarme\Model\Payment\Voucher;
use Woocommerce\Pagarme\Block\Checkout\Gateway;

defined( 'ABSPATH' ) || exit;

/**
 * Class Card
 * @package Woocommerce\Pagarme\Block\Checkout\Form
 */
class Card extends Gateway
{
    const INVALID_CARD_ERROR_MESSAGE = 'This card number is invalid.';

    /**
     * @var string
     */
    protected $_template = 'templates/checkout/form/card';

    /**
     * @var string[]
     */
    protected $scripts = ['checkout/model/payment/card', 'checkout/model/payment/card/tokenize'];

    /**
     * @var string[]
     */
    protected $deps = [];

    /** @var int  */
    protected $sequence = 1;

    public function enqueue_scripts($scripts = null, $deps = [])
    {
        parent::enqueue_scripts($scripts, $deps);

        wp_localize_script(
            WCMP_JS_HANDLER_BASE_NAME . 'card',
            'PagarmeGlobalVars',
            self::getLocalizeScriptArgs()
        );
    }

    /**
     * @param int $sequence
     * @return $this
     */
    public function setSequence(int $sequence)
    {
        return $this->setData('sequence', $sequence);
    }

    /**
     * @return int
     */
    public function getSequence()
    {
        if (!$this->getData('sequence')) {
            return $this->sequence;
        }
        return $this->getData('sequence');
    }

    /**
     * @param int $qtyCards
     * @return $this
     */
    public function setQuantityCards(int $qtyCards = 1)
    {
        if ($qtyCards > 1) {
            $this->setShowOrderValue(true);
        }
        return $this->setData('qty_cards', $qtyCards);
    }

    /**
     * @return int
     */
    public function getQuantityCards()
    {
        if ($this->getQtyCards() > 1) {
            $this->setShowOrderValue(true);
        }
        return $this->getData('qty_cards');
    }

    /**
     * @return bool
     */
    public function showInstallments()
    {
        $hideInstallmentsInMethods = [
            Voucher::PAYMENT_CODE
        ];
        $isHideInstallments = in_array($this->getPaymentInstance()->getMethodCode(), $hideInstallmentsInMethods);

        if (Subscription::isChangePaymentSubscription() || $isHideInstallments) {
            return false;
        }
        return true;
    }

    public function showHolderName()
    {
        $methods = [
            Voucher::PAYMENT_CODE
        ];
        if (in_array($this->getPaymentInstance()->getMethodCode(), $methods)) {
            return true;
        }
        return false;
    }

    /**
     * @param string|null $id
     * @return string
     */
    public function getElementId(string $id = null)
    {
        $elementId = '[cards][' . $this->getSequence() . ']';
        if ($id) {
            $elementId .= '[' . $id . ']';
        }
        return parent::getElementId($elementId);
    }

    /**
     * @return String
     */
    public function getCompoenent()
    {
        return Utils::get_component('pagarme-checkout');
    }

    public static function getLocalizeScriptArgs($args = array())
    {
        $defaults = array(
            'ajaxUrl'        => Utils::get_admin_url('admin-ajax.php'),
            'WPLANG'         => get_locale(),
            'spinnerUrl'     => Core::plugins_url('assets/images/icons/spinner.png'),
            'prefix'         => Core::PREFIX,
            'checkoutErrors' => array(
                'pt_BR' => self::getCardErrorsMessagesTranslated(),
            ),
        );

        return array_merge($defaults, $args);
    }

    public static function getCardErrorsMessagesTranslated()
    {
        return array(
            'exp_month: A value is required.' =>
                __('Expiration Date: The month is required.', 'woo-pagarme-payments'),
            'card.exp_month: The field exp_month must be between 1 and 12.' =>
                __('The field exp_month must be between 1 and 12.', 'woo-pagarme-payments'),
            'exp_month: The field exp_month must be between 1 and 12.'    =>
                __('Expiration Date: The month must be between 1 and 12.', 'woo-pagarme-payments'),
            "exp_year: The value 'undefined' is not valid for exp_year." =>
                __('Expiration Date: Invalid year.', 'woo-pagarme-payments'),
            'request: The card expiration date is invalid.' =>
                __('Expiration Date: Invalid expiration date.', 'woo-pagarme-payments'),
            'request: Card expired.' =>
                __('Expiration Date: Expired card.', 'woo-pagarme-payments'),
            'holder_name: The holder_name field is required.' =>
                __('The card holder name is required.', 'woo-pagarme-payments'),
            'card.holder_name: The holder_name field is required.' =>
                __('The holder_name field is required.', 'woo-pagarme-payments'),
            'number: The number field is required.' =>
                __('The card number is required.', 'woo-pagarme-payments'),
            'number: The number field is not a valid credit card number.' =>
                __(self::INVALID_CARD_ERROR_MESSAGE, 'woo-pagarme-payments'),
            'card: The number field is not a valid card number' =>
                __(self::INVALID_CARD_ERROR_MESSAGE, 'woo-pagarme-payments'),
            'card.number: The number field is required.' =>
            __('The number field is required.', 'woo-pagarme-payments'),
            'card.number: The number field is not a valid number.' =>
            __('The number field is not a valid number.', 'woo-pagarme-payments'),
            'card.number: The field number must be a string with a minimum length of 13 and a maximum length of 19.'
            => __('The card number must be between 13 and 19 characters.', 'woo-pagarme-payments'),
            'card: Card expired.' =>
                __('The expiration date is expired.', 'woo-pagarme-payments'),
            'card.cvv: The field cvv must be a string with a minimum length of 3 and a maximum length of 4.'
            => __('The card code must be between 3 and 4 characters.', 'woo-pagarme-payments'),
            'card.cvv: The cvv field is not a valid number.' =>
            __('The cvv field is not a valid number.', 'woo-pagarme-payments'),
            'card: Invalid data to change card brand' =>
                __(self::INVALID_CARD_ERROR_MESSAGE, 'woo-pagarme-payments'),
            'card: Tokenize timeout' =>
                __('Tokenization timeout.', 'woo-pagarme-payments'),
            'fail_get_token' =>
                __('Failed to generate Token for 3DS, try again.', 'woo-pagarme-payments'),
            'fail_get_email' =>
                __('There was a problem finding the email.', 'woo-pagarme-payments'),
            'fail_get_billing_address' =>
                __('There was a problem finding the address.', 'woo-pagarme-payments'),
            'fail_assemble_card_expiry_date' =>
                __('There was a problem when assembling the card\'s expiration data.', 'woo-pagarme-payments'),
            'fail_assemble_purchase' =>
                __('There was a problem when assembling the purchase data.', 'woo-pagarme-payments'),
            'invalidBrand' =>
                __('Invalid Data', 'woo-pagarme-payments'),
            'card: Can\'t check card form: Invalid element received' =>
                __('Can\'t check card form: Invalid element received.', 'woo-pagarme-payments'),
            'serviceUnavailable' =>
                __('Unable to generate a transaction. Unavailable service.', 'woo-pagarme-payments'),
            'creditCardFormHasErrors' =>
                __('Please, check the errors below.', 'woo-pagarme-payments')
        );
    }
}
