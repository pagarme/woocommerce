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

defined( 'ABSPATH' ) || exit;

/**
 * Class Pix
 * @package Woocommerce\Pagarme\Block\Order
 */
class Pix extends AbstractTransaction
{
    /**
     * @var string
     */
    protected $_template = 'templates/order/transaction/pix';

    /**
     * @return string|null
     */
    public function getQrCodeUrl()
    {
        try {
            if ($this->getTransaction() && $this->getTransaction()->getPostData()) {
                $postData = $this->getTransaction()->getPostData();
                if (property_exists($postData, 'tran_data')) {
                    $data = $this->jsonSerialize->unserialize($postData->tran_data);
                    return $data['qr_code_url'];
                }
            }
        } catch (\Exception $e) {}
        return null;
    }

    /**
     * @return string|null
     */
    public function getRawQrCode()
    {
        try {
            if ($this->getTransaction() && $this->getTransaction()->getPostData()) {
                $postData = $this->getTransaction()->getPostData();
                if (property_exists($postData, 'tran_data')) {
                    $data = $this->jsonSerialize->unserialize($postData->tran_data);
                    return $data['qr_code'];
                }
            }
        } catch (\Exception $e) {}
        return null;
    }

    /**
     * @return array
     */
    public function getInstructions()
    {
        return [
            '1. Point your phone at this screen to capture the code.',
            '2. Open your payments app.',
            '3. Confirm the information and complete the payment on the app.',
            '4. We will send you a purchase confirmation.'
        ];
    }
}
