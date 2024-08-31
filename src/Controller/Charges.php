<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Core;
use Pagarme\Core\Kernel\Services\ChargeService;
use Pagarme\Core\Kernel\Services\MoneyService;
use Woocommerce\Pagarme\Model\Charge;
use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup;

class Charges
{
    /**
     * @var Charge
     */
    protected $model;

    public function __construct()
    {
        $this->model = new Charge();
        $this->build_actions();
        WoocommerceCoreSetup::bootstrap();
        add_action('wp_ajax_STW3dqRT6E', array($this, 'handle_ajax_operations'));
    }

    public function handle_actions_add_notes($body)
    {
        $this->model->add_notes($body);
    }

    public function handle_actions($body)
    {
        $this->model->create_from_webhook($body);
    }

    public function handle_ajax_operations()
    {
        if (!Utils::is_request_ajax()) {
            exit(0);
        }

        $charge_id = Utils::post('charge_id', false);
        $amount    = Utils::post('amount', 0);
        $mode      = Utils::post('mode', false);

        if (!$charge_id) {
            http_response_code(412);
            Utils::error_server_json('empty_charge_id', 'É necessário informar o ID da charge.');
            exit(1);
        }

        if (!in_array($mode, ['capture', 'cancel'])) {
            http_response_code(412);
            Utils::error_server_json('invalid_mode', 'Operação inválida!');
            exit(1);
        }

        $method = "handle_charge_" . $mode;
        $response = $this->$method($charge_id, $amount);

        error_log(print_r($response, true));

        if (!$response->isSuccess()) {
            http_response_code(412);
            Utils::error_server_json('operation_error', 'Não foi possível efetuar esta operação!');
            exit(1);
        }

        wp_send_json_success([
            'mode'    => $mode,
            'message' => 'Operação efetuada com sucesso!',
        ]);
    }

    private function handle_charge_cancel($charge_id, $amount)
    {
        $chargeService = new ChargeService();
        $moneyService = new MoneyService();

        $amount = $moneyService->removeSeparators($amount);
        $amount = $moneyService->floatToCents($amount / 100);
        return $chargeService->cancelById($charge_id, $amount);
    }

    private function handle_charge_capture($charge_id, $amount)
    {
        $chargeService = new ChargeService();
        $moneyService = new MoneyService();

        $amount = $moneyService->removeSeparators($amount);
        $amount = $moneyService->floatToCents($amount / 100);

        return $chargeService->captureById($charge_id, $amount);
    }

    private function build_actions()
    {
        $events = array(
            'charge_created',
            'charge_updated',
            'charge_paid',
            'charge_pending',
            'charge_payment_failed',
            'charge_chargedback',
        );

        foreach ($events as $event) {
            add_action("on_pagarme_{$event}", array($this, 'handle_actions'));
        }

        $eventsNotes = array(
            'charge_antifraud_approved',
            'charge_antifraud_manual',
            'charge_antifraud_pending',
            'charge_antifraud_reproved',
        );

        foreach ($eventsNotes as $event) {
            add_action("on_pagarme_notes_{$event}", array($this, 'handle_actions_add_notes'));
        }
    }
}
