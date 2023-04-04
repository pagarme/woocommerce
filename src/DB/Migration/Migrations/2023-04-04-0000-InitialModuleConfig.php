<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\DB\Migration\Migrations;

use Woocommerce\Pagarme\DB\Migration\AbstractMigration;
use Woocommerce\Pagarme\DB\Migration\MigrationInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Class InitialModuleConfig
 * @package Woocommerce\Pagarme\DB\Migration\Migrations
 */
class InitialModuleConfig extends AbstractMigration implements MigrationInterface
{

    /** @var string */
    const PARAM = 'initial_config';

    /**
     * Apply the migrations.
     * @return void
     */
    public function apply(): void
    {
        if ($this->validate()) {
            $data = [
                'multicustomers' => '0',
                'enable_logs' => '0',
                'enable_pix' => 'no',
                'pix_title' => 'Pix',
                'pix_qrcode_expiration_time' => 3500,
                'pix_additional_data' => 'Custom Store PIX',
                'enable_credit_card' => 'no',
                'credit_card_title' => 'Credit Card',
                'cc_operation_type' => '2',
                'cc_soft_descriptor' => 'Credit Card',
                'cc_installment_type' => '1',
                'cc_installments_maximum' => '1',
                'cc_installments_min_amount' => '',
                'cc_installments_interest' => '',
                'cc_installments_interest_increase' => '',
                'cc_installments_without_interest' => '',
                'cc_allow_save' => '0',
                'antifraud_enabled' => '0',
                'antifraud_min_value' => '',
                self::PARAM => true
            ];
            $this->settings->addData($data)->save();
        }
    }

    /**
     * Checks if the migration has already been run. Returns true if not.
     * @return bool
     */
    public function validate(): bool
    {
        if (!$this->settings->getData(self::PARAM)) {
            return true;
        }
        return false;
    }
}
