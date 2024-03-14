<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Pagarme\Core\Payment\Repositories\CustomerRepository as CoreCustomerRepository;
use Pagarme\Core\Payment\Repositories\SavedCardRepository as CoreSavedCardRepository;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Account;
use Woocommerce\Pagarme\Model\Customer;
use Woocommerce\Pagarme\Block\Template;

class Accounts
{
    protected $wallet_endpoint;
    protected $card_repository;
    protected $template;

    const WALLET_ENDPOINT = 'wallet-pagarme';

    const OPT_WALLET_ENDPOINT = 'woocommerce_pagarme_wallet_endpoint';

    public function __construct(
        Template $template = null
    )
    {
        $this->template = $template ?? new Template();

        $this->wallet_endpoint = get_option(self::OPT_WALLET_ENDPOINT, self::WALLET_ENDPOINT);
        $this->card_repository = new CoreSavedCardRepository();
        add_action('init', array($this, 'add_endpoints'));
        add_filter('woocommerce_account_settings', array($this, 'settings_account'));
        add_filter('woocommerce_account_menu_items', array($this, 'menu_items'));
        add_filter('woocommerce_get_query_vars', array($this, 'query_vars'));
        add_action("woocommerce_account_{$this->wallet_endpoint}_endpoint", array($this, 'wallet_content'));
        add_action('woocommerce_api_' . Account::WALLET_ENDPOINT, array($this, 'remove_credit_card'));
    }

    public function menu_items($items)
    {
        $last_value = end($items);
        $last_key   = key($items);

        unset($items[$last_key]);

        $items[$this->wallet_endpoint] = __('Wallet', 'woo-pagarme-payments');
        $items[$last_key]              = $last_value;

        return $items;
    }

    public function query_vars($vars)
    {
        $vars[$this->wallet_endpoint] = $this->wallet_endpoint;

        return $vars;
    }

    public function add_endpoints()
    {
        global $woocommerce;

        add_rewrite_endpoint($this->wallet_endpoint, $woocommerce->query->get_endpoints_mask());
    }

    public function settings_account($settings)
    {
        $wallet = array(
            'title'    => __('Wallet', 'woo-pagarme-payments'),
            'desc'     => __('Your wallet for Pagar.me registered credit cards', 'woo-pagarme-payments'),
            'id'       => self::OPT_WALLET_ENDPOINT,
            'type'     => 'text',
            'default'  => $this->wallet_endpoint,
            'desc_tip' => true,
        );

        array_splice($settings, count($settings) - 2, 0, array($wallet));

        return $settings;
    }

    public function wallet_content()
    {
        $this->template->createBlock(
            \Woocommerce\Pagarme\Block\Account\Wallet::class,
            'pagarme-wallet'
        )->toHtml();
    }

    public function remove_credit_card()
    {
        if (!Utils::is_request_ajax() || Utils::server('REQUEST_METHOD') !== 'POST') {
            exit(0);
        }

        if (!is_user_logged_in()) {
            wp_send_json_error(__('User not loggedin.', 'woo-pagarme-payments'));
        }

        $customer    = new Customer(get_current_user_id(), new CoreSavedCardRepository(), new CoreCustomerRepository());
        $saved_cards = $customer->cards;
        $card_id     = Utils::post('card_id');
        $card_found = false;
        foreach ($saved_cards as $saved_card) {
            $saved_card_id = $saved_card->getPagarmeId()->getValue();
            if ($saved_card_id === $card_id) {
                $this->remove_core_card($customer, $saved_card);
                wp_send_json_success(
                    __('Card removed successfully.', 'woo-pagarme-payments')
                );
                $card_found = true;
                break;
            }
        }
        if (!$card_found) {
            wp_send_json_error(__('Card not found.', 'woo-pagarme-payments'));
        }
    }

    private function remove_core_card(&$customer, $saved_card)
    {
        $this->card_repository->delete($saved_card);
        $updatedWallet = array_filter($customer->cards, function ($card) use ($saved_card) {
            return $card->getPagarmeId() !== $saved_card->getPagarmeId();
        });

        $customer->cards = $updatedWallet;
    }
}
