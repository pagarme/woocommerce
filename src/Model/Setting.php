<?php

namespace Woocommerce\Pagarme\Model;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Custom_Gateway;

use WC_Logger;

class Setting
{
    public static $_instance = null;

    private $_settings;

    private $_fields = array(
        'enabled'                           => array(),
        'title'                             => array(),
        'description'                       => array(),
        'hub_install_id'                    => array(),
        'hub_environment'                   => array(),
        'environment'                       => array(),
        'sandbox_secret_key'                => array(),
        'sandbox_public_key'                => array(),
        'production_secret_key'             => array(),
        'production_public_key'             => array(),
        'account_management_key'            => array(),
        'account_id'                        => array(),
        'is_gateway_integration_type'       => array(),
        'section_payment_settings'          => array(),
        'enable_billet'                     => array(),
        'enable_pix'                        => array(),
        'enable_voucher'                    => array(),
        'voucher_card_wallet'               => array(),
        'voucher_soft_descriptor'           => array(),
        'voucher_flags'                     => array(),
        'pix_qrcode_expiration_time'        => array(),
        'pix_additional_data'               => array(),
        'enable_credit_card'                => array(),
        'multimethods_billet_card'          => array(),
        'multimethods_2_cards'              => array(),
        'multicustomers'                    => array(),
        'antifraud_enabled'                 => array(),
        'antifraud_min_value'               => array(),
        'billet_bank'                       => array(),
        'billet_instructions'               => array(),
        'billet_deadline_days'              => array(),
        'cc_soft_descriptor'                => array(),
        'cc_operation_type'                 => array(),
        'cc_flags'                          => array(),
        'cc_allow_save'                     => array(),
        'cc_installment_type'               => ['sanitize' => 'intval'],
        'cc_installments_maximum'           => ['sanitize' => 'intval'],
        'cc_installments_without_interest'  => ['sanitize' => 'intval'],
        'cc_installments_interest'          => ['sanitize' => 'floatval'],
        'cc_installments_min_amount'        => ['sanitize' => 'floatval'],
        'cc_installments_interest_increase' => ['sanitize' => 'floatval'],
        'cc_installments_by_flag'           => array(),
        'webhook_id'                        => array(),
        'enable_logs'                       => array(),
        'migrations'                        => array(),
    );

    private function __construct($settings)
    {
        $this->set_settings($settings);
    }

    public function __get($key)
    {
        if (isset($this->{$key})) {
            return $this->{$key};
        }

        return $this->_get_property($key);
    }

    public function set($key, $value)
    {
        if (!$this->is_valid_key($key)) {
            return;
        }

        $settings = $this->get_settings();

        $settings[$key] = Utils::rm_tags($value);

        $this->update_settings($settings);
    }

    public function delete($key)
    {
        $settings = $this->get_settings();

        if (!isset($settings[$key])) {
            return;
        }

        unset($settings[$key]);

        $this->update_settings($settings);
    }

    private function _get_property($key)
    {
        if (!$this->is_valid_key($key)) {
            return false;
        }

        $sanitize     = Utils::get_value_by($this->_fields[$key], 'sanitize');
        $value        = Utils::get_value_by($this->get_settings(), $key);
        $this->{$key} = Utils::sanitize($value, $sanitize);

        return $this->{$key};
    }

    public function get_option_key()
    {
        return Core::tag_name('settings');
    }

    public function get_flags_list()
    {
        //Some brands are hidden for PSP
        return array(
            'visa'              => 'Visa',
            'mastercard'        => 'MasterCard',
            'amex'              => 'Amex',
            'hipercard'         => 'HiperCard',
            'diners'            => 'Diners',
            'elo'               => 'Elo',
            'discover'          => 'Discover',
            'aura'              => 'Aura',
            'jcb'               => 'JCB',
            'credz'             => 'Credz',
            // 'sodexoalimentacao' => 'SodexoAlimentacao',
            // 'sodexocultura'     => 'SodexoCultura',
            // 'sodexogift'        => 'SodexoGift',
            // 'sodexopremium'     => 'SodexoPremium',
            // 'sodexorefeicao'    => 'SodexoRefeicao',
            // 'sodexocombustivel' => 'SodexoCombustivel',
            // 'vr'                => 'VR',
            // 'alelo'             => 'Alelo',
            'banese'            => 'Banese',
            'cabal'             => 'Cabal',
        );
    }

    public function get_voucher_flags_list()
    {
        return array(
            'alelo'             => 'Alelo',
            'sodexoalimentacao' => 'SodexoAlimentacao',
            'sodexocultura'     => 'SodexoCultura',
            'sodexogift'        => 'SodexoGift',
            'sodexopremium'     => 'SodexoPremium',
            'sodexorefeicao'    => 'SodexoRefeicao',
            'sodexocombustivel' => 'SodexoCombustivel',
            'vr'                => 'VR'

        );
    }

    public function set_settings($settings)
    {
        $this->_settings = ($settings) ? $settings : get_option($this->get_option_key());
    }

    public function get_settings()
    {
        return empty($this->_settings) ? array() : $this->_settings;
    }

    public function update_settings(array $settings)
    {
        update_option($this->get_option_key(), $settings);

        $this->set_settings($settings);
    }

    public function log()
    {
        return new \WC_Logger();
    }

    public function is_enabled()
    {
        return ('yes' === $this->__get('enabled'));
    }

    public function is_gateway_integration_type()
    {
        return ('yes' === $this->__get('is_gateway_integration_type'));
    }

    public function is_enabled_logs()
    {
        return ('yes' === $this->__get('enable_logs'));
    }

    public function is_active_credit_card()
    {
        return ('yes' === $this->__get('enable_credit_card'));
    }

    public function is_allowed_save_credit_card()
    {
        return ('yes' === $this->__get('cc_allow_save'));
    }

    public function is_allowed_save_voucher_card()
    {
        return ('yes' === $this->__get('voucher_card_wallet'));
    }

    public function is_active_billet()
    {
        return ('yes' === $this->__get('enable_billet'));
    }

    public function is_active_billet_and_card()
    {
        return ('yes' === $this->__get('multimethods_billet_card'));
    }

    public function is_active_2_cards()
    {
        return ('yes' === $this->__get('multimethods_2_cards'));
    }

    public function is_active_pix()
    {
        return ('yes' === $this->__get('enable_pix'));
    }

    public function is_active_voucher()
    {
        return ('yes' === $this->__get('enable_voucher'));
    }

    public function is_active_capture()
    {
        $operation_type = $this->__get('cc_operation_type');

        return intval($operation_type) === 1 ? false : true;
    }

    public function is_active_multicustomers()
    {
        return ('yes' === $this->__get('multicustomers'));
    }

    public function is_valid_key($key)
    {
        return isset($this->_fields[$key]);
    }

    public function isAntifraudEnabled()
    {
        return ($this->__get('antifraud_enabled') === 'yes' ? true : false);
    }

    public function isInstallmentsDefaultConfig()
    {
        return ($this->__get('cc_installment_type') === Gateway::CC_TYPE_BY_FLAG ? false : true);
    }

    public function isCardStatementDescriptor()
    {
        return $this->__get('cc_soft_descriptor');
    }

    public function isVoucherStatementDescriptor()
    {
        return $this->__get('voucher_soft_descriptor');
    }

    public function isHubEnabled()
    {
        return !empty($this->__get('hub_install_id'));
    }

    /**
     * Get the secret key according to the environment
     *
     * @return string
     */
    public function get_secret_key()
    {
        return $this->__get($this->is_sandbox() ? 'sandbox_secret_key' : 'production_secret_key');
    }

    /**
     * Get the public key according to the environment
     *
     * @return string
     */
    public function get_public_key()
    {
        return $this->__get($this->is_sandbox() ? 'sandbox_public_key' : 'production_public_key');
    }

    public function is_sandbox()
    {
        return ($this->__get('environment') === 'sandbox');
    }

    public function get_active_tab()
    {
        switch (Utils::get('tab')) {
            case 'creditCard':
                return 1;

            case 'boleto':
                return 2;

            case 'pix':
                return 3;

            case 'billetAndCard':
                return 4;

            case '2cards':
                return 5;

            case 'voucher':
                return 6;

            default:
                return 0;
        }
    }

    public function getCardOperationForCore()
    {
        return ($this->is_active_capture() ? 'auth_and_capture' : 'auth_only');
    }

    public static function get_instance($settings = false)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($settings);
        }

        return self::$_instance;
    }
}
