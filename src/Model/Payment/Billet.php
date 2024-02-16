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

use WC_Order;

defined( 'ABSPATH' ) || exit;

/**
 *  Class Billet
 * @package Woocommerce\Pagarme\Model\Payment
 */
class Billet extends AbstractPaymentWithCheckoutInstructions implements PaymentInterface
{
    /** @var string */
    const PAYMENT_CODE = 'billet';

    /** @var string */
    const IMAGE_FILE_NAME = 'barcode.svg';

    /** @var int */
    protected $suffix = 5;

    /** @var string */
    protected $name = 'Billet';

    /** @var string */
    protected $code = self::PAYMENT_CODE;

    /** @var string[] */
    protected $requirementsData = [
        'multicustomer_billet',
        'payment_method',
        'enable_multicustomers_billet'
    ];

    /** @var array */
    protected $dictionary = [];

    /**
     * @return string
     */
    public static function getDefaultCheckoutInstructions()
    {
        return __(
            'The billet will be displayed after purchase confirmation and can be paid at any bank agency'
            . ', via your smartphone or computer through digital banking services.',
            'woo-pagarme-payments'
        );
    }

    public function getPayRequestBase(WC_Order $wc_order, array $form_fields, $customer = null)
    {
        $expirationDate = new \DateTime();
        $days = (int) $this->getConfig()->getBilletDeadlineDays();
        if ($days) {
            $expirationDate->modify("+{$days} day");
        }
        return [
            'payment_method' => 'boleto',
            'boleto' => [
                'bank' => $this->getConfig()->getBilletBank(),
                'instructions' => $this->getConfig()->getBilletInstructions(),
                'due_at' => $expirationDate->format('c')
            ]
        ];
    }
}
