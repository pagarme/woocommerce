<?php

namespace Woocommerce\Pagarme\Concrete;

use Exception;
use Pagarme\Core\Kernel\Abstractions\AbstractPlatformOrderDecorator;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Interfaces\PlatformInvoiceInterface;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Kernel\ValueObjects\OrderState;
use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use Pagarme\Core\Marketplace\Aggregates\Split;
use Pagarme\Core\Payment\Aggregates\Address;
use Pagarme\Core\Payment\Aggregates\Customer;
use Pagarme\Core\Payment\Aggregates\Item;
use Pagarme\Core\Payment\Aggregates\Payments\AbstractCreditCardPayment;
use Pagarme\Core\Payment\Aggregates\Payments\AbstractPayment;
use Pagarme\Core\Payment\Aggregates\Payments\BoletoPayment;
use Pagarme\Core\Payment\Aggregates\Payments\NewDebitCardPayment;
use Pagarme\Core\Payment\Aggregates\Payments\NewVoucherPayment;
use Pagarme\Core\Payment\Aggregates\Payments\PixPayment;
use Pagarme\Core\Payment\Aggregates\Shipping;
use Pagarme\Core\Payment\Factories\PaymentFactory;
use Pagarme\Core\Payment\Repositories\CustomerRepository as CoreCustomerRepository;
use Pagarme\Core\Payment\ValueObjects\CustomerPhones;
use Pagarme\Core\Payment\ValueObjects\CustomerType;
use Pagarme\Core\Payment\ValueObjects\Phone;
use Pagarme\Core\Recurrence\Services\RecurrenceService;
use stdClass;
use Throwable;
use WC_Customer;
use WC_Order;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Customer as PagarmeCustomer;
use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Payment as WCModelPayment;

class WoocommercePlatformOrderDecorator extends AbstractPlatformOrderDecorator
{
    /** @var WC_Order */
    protected $platformOrder;

    private $i18n;

    private $formData;

    private $paymentMethod;

    private $paymentInformation;

    private $orderService;

    /** @var Customer */
    private $customer;

    public function __construct($formData = null, $paymentMethod = null)
    {
        $this->i18n          = new LocalizationService();
        $this->formData      = $formData;
        $this->paymentMethod = $this->formatPaymentMethod($paymentMethod);
        $this->orderService  = new OrderService();
        parent::__construct();
    }

    private function formatPaymentMethod($paymentMethod)
    {
        if (empty($paymentMethod)) {
            return "";
        }
        $paymentMethodParts    = explode('_', $paymentMethod);
        $formatedPaymentMethod = '';

        foreach ($paymentMethodParts as $part) {
            $formatedPaymentMethod .= ucfirst($part);
        }

        $formatedPaymentMethod = lcfirst($formatedPaymentMethod);

        return $formatedPaymentMethod;
    }

    public function save()
    {
        $this->getPlatformOrder()->save();
    }

    public function setStateAfterLog(OrderState $state)
    {
        // Woocommmerce doesnt have the concept of state, only status;
        return null;
    }


    /**
     * @return OrderState;
     */
    public function getState()
    {
        $statusToState = [
            'pending'                 => 'stateNew',
            'paid'                    => 'complete',
            'pending_payment'         => 'pending_payment',
            'failed'                  => 'closed',
            'processing'              => 'processing',
            'on_hold'                 => 'holded',
            'canceled'                => 'canceled',
            'refunded'                => 'complete',
            'authentication_required' => 'processing'
        ];

        $status = $this->getStatus();

        $state = $statusToState[$status] ?
            $statusToState[$status] : 'processing';

        return OrderState::$state();
    }

    private function getWoocommerceStatusFromCoreStatus($coreStatus)
    {
        $coreToWoocommerceStatus = array(
            'canceled' => 'cancelled',
            'pending'  => 'on-hold'
        );

        return array_key_exists($coreStatus, $coreToWoocommerceStatus) ?
            $coreToWoocommerceStatus[$coreStatus] : $coreStatus;
    }

    private function getCoreStatusFromWoocommerceStatus($woocommerceStatus)
    {
        $woocommerceToCoreStatus = array(
            'cancelled' => 'canceled',
            'on-hold'   => 'pending'
        );

        return array_key_exists($woocommerceStatus, $woocommerceToCoreStatus) ?
            $woocommerceToCoreStatus[$woocommerceStatus] : $woocommerceStatus;
    }

    public function setStatusAfterLog(OrderStatus $status)
    {
        $log                     = new LogService('Order', true);
        $stringCoreStatus        = $status->getStatus();
        $stringWoocommerceStatus = $this->getWoocommerceStatusFromCoreStatus($stringCoreStatus);
        if ($this->getPlatformOrder()->get_status() === 'completed') {
            $log->info('Impediment to change the order status to ' . $status->getStatus() . '. Order is complete.');

            return;
        }
        $order = new Order($this->getPlatformOrder()->get_id());
        if ($stringWoocommerceStatus === 'processing' && !$order->needs_processing()) {
            $log->info('Order does not need processing. Changing status to complete.');
            $stringWoocommerceStatus = $this->getWoocommerceStatusFromCoreStatus('completed');
        }
        $this->getPlatformOrder()->set_status($stringWoocommerceStatus);
    }

    public function getStatus()
    {
        $woocommerceStatus = $this->getPlatformOrder()->get_status();
        $coreStatus        = $this->getCoreStatusFromWoocommerceStatus($woocommerceStatus);

        return $coreStatus;
    }

    public function loadByIncrementId($incrementId)
    {
        $wcOrder = new WC_Order($incrementId);
        $this->setPlatformOrder($wcOrder);
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function sendEmail($message)
    {
        // we don't have the email functionallity in woocommerce module
        return null;
    }

    /**
     * @param OrderStatus $orderStatus
     *
     * @return string
     */
    public function getStatusLabel(OrderStatus $orderStatus)
    {
        return wc_get_order_status_name($orderStatus->getStatus());
    }

    /**
     * @param $message
     * @param bool $notifyCustomer
     */
    protected function addMPHistoryComment($message, $notifyCustomer = false)
    {
        $this->getPlatformOrder()->add_order_note($message);
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    public function setAdditionalInformation($name, $value)
    {
        return null;
    }

    /**
     * @param Charge[] $charges
     *
     * @return array[['key' => value]]
     */
    public function extractAdditionalChargeInformation(array $charges)
    {
        return null;
    }

    /**
     * @param Charge[] $charges
     */
    public function addAdditionalInformation(array $charges)
    {
        // woocommerce doesnt have the additional information table;
        return null;
    }

    public function setIsCustomerNotified()
    {
        // TODO: Implement setIsCustomerNotified() method.
    }

    public function canInvoice()
    {
        // woocommerce doesnt have invoice concept
        return true;
    }

    public function getIncrementId()
    {
        return $this->getPlatformOrder()->get_id();
    }

    public function getGrandTotal()
    {
        return $this->getPlatformOrder()->get_total();
    }


    public function getBaseTaxAmount()
    {
        return $this->getPlatformOrder()->get_total_tax();
    }

    public function getTotalPaid()
    {
        $platformOrder = $this->getPlatformOrder();

        return $platformOrder->is_paid() ? $platformOrder->get_total() : 0;
    }

    public function getTotalDue()
    {
        $platformOrder = $this->getPlatformOrder();

        return !$platformOrder->is_paid() ? $platformOrder->get_total() : 0;
    }

    public function setTotalPaid($amount)
    {
        // WC doesnt have the a payment entity or partial payment handling;
        return null;
    }

    public function setBaseTotalPaid($amount)
    {
        // WC doesnt have the a payment entity or partial payment handling;
        return null;
    }

    public function setTotalDue($amount)
    {
        // WC doesnt have the a payment entity or partial payment handling;
        return null;
    }

    public function setBaseTotalDue($amount)
    {
        // WC doesnt have the a payment entity or partial payment handling;
        return null;
    }

    public function setTotalCanceled($amount)
    {
        // WC doesnt have the a payment entity or partial payment handling;
        return null;
    }

    public function setBaseTotalCanceled($amount)
    {
        // WC doesnt have the a payment entity or partial payment handling;
        return null;
    }

    public function getTotalRefunded()
    {
        return $this->getPlatformOrder()->get_total_refunded();
    }

    public function setTotalRefunded($amount)
    {
        // WC doesnt have the a payment entity or partial payment handling;
        return null;
    }

    public function setBaseTotalRefunded($amount)
    {
        // WC doesnt have the a payment entity or partial payment handling;
        return null;
    }

    public function getCode()
    {
        return $this->getPlatformOrder()->get_id();
    }

    public function canUnhold()
    {
        // WC doesnt have a "hold" concept to an order;
        return null;
    }

    public function isPaymentReview()
    {
        // WC doesnt have a "review" concept to an order;
        return null;
    }

    public function isCanceled()
    {
        return $this->getStatus() === "canceled";
    }

    /**
     * @return string
     */
    public function getPaymentMethodPlatform()
    {
        return $this->paymentMethod;
    }

    /**
     * @return PlatformInvoiceInterface[]
     */
    public function getInvoiceCollection()
    {
        // Woocommerce doesnt have an invoice feature by default;
        return [];
    }

    /**
     * @return OrderId|null
     */
    public function getPagarmeId()
    {
        $orderId = $this->platformOrder->get_id();
        if (empty($orderId)) {
            return null;
        }

        $order = new Order($orderId);

        if (empty($order)) {
            return null;
        }

        return $order->pagarme_id;
    }

    public function getHistoryCommentCollection()
    {
        return $this->getPlatformOrder()->get_customer_order_notes();
    }

    public function getData()
    {
        return $this->getPlatformOrder()->get_data();
    }

    public function getTransactionCollection()
    {
        //WC doesnt have transaction entity, only order;
        return [];
    }

    public function getPaymentCollection()
    {
        // WC doesnt have payment entity, only order;
        // TODO: work-around this one, might be used for other things in core.
        return [];
    }

    /** @return Customer */
    public function getCustomer()
    {
        if (!empty($this->customer)) {
            return $this->customer;
        }

        $customerId = get_current_user_id();
        if (!empty($this->getPlatformOrder()->get_user_id())) {
            $customerId = $this->getPlatformOrder()->get_user_id() ?? null;
        }
        if (!empty($customerId)) {
            $customer = $this->getRegisteredCustomer($customerId);
            $this->setCustomer($customer);

            return $customer;
        }

        $customer = $this->getGuestCustomer();
        $this->setCustomer($customer);

        return $customer;
    }

    /**
     * @param Customer $customer
     *
     * @return void
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @param PagarmeCustomer $pagarmeCustomer
     *
     * @return Customer
     * @throws Exception
     */
    private function getRegisteredCustomer($woocommerceCustomerId)
    {
        $order = new Order($this->getPlatformOrder()->get_id());

        $address  = Utils::build_customer_address_from_order($order);
        $document = Utils::build_document_from_order($order);
        $phones   = Utils::build_customer_phones_from_order($order);
        if (empty($document['value'])) {
            $customerPlatform  = new WC_Customer($woocommerceCustomerId);
            $document['value'] = $customerPlatform->get_meta("billing_cpf") ??
                                 $customerPlatform->get_meta("billing_cnpj");
        }
        $homeNumber   = $phones["home_phone"]["complete_phone"];
        $mobileNumber = $phones["mobile_phone"]["complete_phone"];

        $customerRepository = new CoreCustomerRepository();

        $savedCustomer = $customerRepository->findByCode($woocommerceCustomerId);

        $customer = new Customer;
        $customer->setCode($woocommerceCustomerId);
        if ($savedCustomer) {
            $customer = $savedCustomer;
        }

        $fullName = "{$order->billing_first_name} {$order->billing_last_name}";
        $fullName = substr($fullName, 0, 64);
        $fullName = preg_replace("/  /", " ", $fullName);

        $customer->setName($fullName);
        $customer->setEmail(substr($order->billing_email, 0, 64));

        $cleanDocument = preg_replace(
            '/\D/',
            '',
            $document['value']
        );

        $customer->setDocument($cleanDocument);
        $customer->setType(CustomerType::individual());

        $homePhone   = new Phone($homeNumber);
        $mobilePhone = new Phone($mobileNumber);

        $customer->setPhones(
            CustomerPhones::create([$homePhone, $mobilePhone])
        );

        $address = $this->getAddress($address);

        $customer->setAddress($address);

        return $customer;
    }

    /**
     * @return Customer
     * @throws Exception
     */
    private function getGuestCustomer()
    {
        $order = new Order($this->getPlatformOrder()->get_id());

        $address  = Utils::build_customer_address_from_order($order);
        $document = Utils::build_document_from_order($order);
        $phones   = Utils::build_customer_phones_from_order($order);

        $homeNumber   = $phones["home_phone"]["complete_phone"];
        $mobileNumber = $phones["mobile_phone"]["complete_phone"];

        $fullName = "{$order->billing_first_name} {$order->billing_last_name}";
        $fullName = substr($fullName, 0, 64);
        $fullName = preg_replace("/  /", " ", $fullName);

        $email = substr($order->billing_email, 0, 64);

        $customer = new Customer();

        $customer->setName($fullName);
        $customer->setEmail($email);

        $cleanDocument = preg_replace(
            '/\D/',
            '',
            $document["value"]
        );

        $customer->setDocument($cleanDocument);
        $customer->setType(CustomerType::individual());

        $homePhone   = new Phone($homeNumber);
        $mobilePhone = new Phone($mobileNumber);

        $customer->setPhones(
            CustomerPhones::create([$homePhone, $mobilePhone])
        );

        $address = $this->getAddress($address);
        $customer->setAddress($address);

        return $customer;
    }

    /** @return Item[] */
    public function getItemCollection()
    {
        $moneyService   = new MoneyService();
        $itemCollection = $this->getPlatformOrder()->get_items();

        foreach ($itemCollection as $woocommerceItem) {
            //adjusting price.
            $woocommerceProduct = $woocommerceItem->get_product();
            $price              = $woocommerceProduct->get_price();
            $price              = $price > 0 ? $price : "0.01";

            if ($price === null) {
                continue;
            }

            $item = new Item;
            $item->setAmount(
                $moneyService->floatToCents($price)
            );

            if (!empty($woocommerceProduct->get_id())) {
                $item->setCode($woocommerceProduct->get_id());
            }

            $itemQuantity = absint($woocommerceItem['qty']);
            $itemName     = sanitize_title($woocommerceItem['name']);

            $item->setQuantity($itemQuantity);
            $item->setDescription(
                $itemName . ' x' . $itemQuantity
            );

            $item->setName($itemName);

            $items[] = $item;
        }

        return $items;
    }

    public function setRecurrenceInfo($item, $quoteItem)
    {
        // we don't have recurrence in woocommmerce;
        return $item;
    }

    public function getRecurrenceService()
    {
        return new RecurrenceService();
    }

    public function getQuote()
    {
        // woocommerce doesnt have a quote concept;
        return null;
    }

    /** @return AbstractPayment[] */
    public function getPaymentMethodCollection()
    {
        $payments = $this->getPaymentCollection();

        if (empty($payments)) {
            $payment         = new WCModelPayment($this->formData['payment_method']);
            $pagarmeCustomer = $this->getCustomer();

            $customer     = new stdClass();
            $customer->id = $pagarmeCustomer->getPagarmeId() ?
                $pagarmeCustomer->getPagarmeId()->getValue() : null;

            $this->paymentInformation = $payment->get_payment_data(
                $this->getPlatformOrder(),
                $this->formData,
                $customer
            );
        }

        $paymentData = [];

        $handler = 'extractPaymentDataFrom' . $this->getPaymentHandler($payments);
        $this->$handler($paymentData);
        $paymentFactory = new PaymentFactory();
        $paymentMethods = $paymentFactory->createFromJson(
            json_encode($paymentData)
        );

        return $paymentMethods;
    }

    private function isBilletAndCreditCardPayment()
    {
        $firstPaymentMethod  = $this->paymentInformation[0]['payment_method'];
        $secondPaymentMethod = $this->paymentInformation[1]['payment_method'];

        return ($firstPaymentMethod === 'boleto' && $secondPaymentMethod === 'credit_card')
               || ($firstPaymentMethod === 'credit_card' && $secondPaymentMethod === 'boleto');
    }

    private function isTwoCreditCardPayment()
    {
        $firstPaymentMethod  = $this->paymentInformation[0]['payment_method'];
        $secondPaymentMethod = $this->paymentInformation[1]['payment_method'];

        return $firstPaymentMethod === 'credit_card' &&
               $secondPaymentMethod === 'credit_card';
    }

    private function isBilletPayment()
    {
        if (count($this->paymentInformation) > 1) {
            return false;
        }

        $payment = $this->paymentInformation[0];

        return $payment['payment_method'] === 'boleto';
    }

    private function isCreditCardPayment()
    {
        if (count($this->paymentInformation) > 1) {
            return false;
        }

        $payment = $this->paymentInformation[0];

        return $payment['payment_method'] === 'credit_card';
    }

    private function isPixPayment()
    {
        if (count($this->paymentInformation) > 1) {
            return false;
        }

        $payment = $this->paymentInformation[0];

        return $payment['payment_method'] === 'pix';
    }

    private function isVoucherPayment()
    {
        if (count($this->paymentInformation) > 1) {
            return false;
        }

        $payment = $this->paymentInformation[0];

        return $payment['payment_method'] === 'voucher';
    }

    private function isGooglepayPayment()
    {
        if (count($this->paymentInformation) > 1) {
            return false;
        }

        $payment = $this->paymentInformation[0];

        return $payment['payment_method'] === 'googlepay';
    }

    private function getPaymentHandler()
    {
        if (count($this->paymentInformation) > 1) {

            if ($this->isBilletAndCreditCardPayment()) {
                return 'BilletCreditCard';
            }

            if ($this->isTwoCreditCardPayment()) {
                return 'TwoCreditCards';
            }
        }

        if ($this->isBilletPayment()) {
            return 'Billet';
        }

        if ($this->isCreditCardPayment()) {
            return 'CreditCard';
        }

        if ($this->isPixPayment()) {
            return 'Pix';
        }

        if ($this->isVoucherPayment()) {
            return 'Voucher';
        }

        if ($this->isGooglepayPayment()) {
            return 'Googlepay';
        }

        return null;
    }

    private function extractPaymentDataFromGooglepay(
        &$paymentData
    ) {
        $newPaymentData = new stdClass();
        $moneyService = new MoneyService();
        $cleanJson =  $this->formData['googlepay']['token'];
        if(!json_decode($this->formData['googlepay']['token'])) {
            $cleanJson =  stripslashes($this->formData['googlepay']['token']);
        }
        $newPaymentData->amount = $moneyService->floatToCents($this->getGrandTotal());
        $newPaymentData->googlepayData = $cleanJson;
        $newPaymentData->billing_address = $this->getCustomer()?->getAddress()?->convertToSDKRequest();
        $newPaymentData->additionalInformation = ["googlepayData" => $cleanJson];
        $googlepayIndex = 'googlepay';
        if (!isset($paymentData[$googlepayIndex])) {
            $paymentData[$googlepayIndex] = [];
        }

        $paymentData[$googlepayIndex][] = $newPaymentData;
    }

    private function extractPaymentDataFromCreditCard(
        &$paymentData
    ) {
        $newPaymentData = $this->extractBasePaymentData();

        $creditCardDataIndex = AbstractCreditCardPayment::getBaseCode();
        if (!isset($paymentData[$creditCardDataIndex])) {
            $paymentData[$creditCardDataIndex] = [];
        }

        if (!empty($this->formData['authentication'])) {
            $authenticationFormData = $this->formData['authentication'];
            $authentication         = new stdClass();
            $authentication->type   = 'threed_secure';
            $authentication->status = $authenticationFormData['trans_status'];

            $threeDSecure                = new stdClass();
            $threeDSecure->mpi           = 'pagarme';
            $threeDSecure->transactionId = $authenticationFormData['tds_server_trans_id'];

            $authentication->threeDSecure   = $threeDSecure;
            $newPaymentData->authentication = $authentication;
        }

        $paymentData[$creditCardDataIndex][] = $newPaymentData;
    }


    private function extractPaymentDataFromPagarmeVoucher(
        $additionalInformation,
        &$paymentData,
        $payment
    ) {
        $newPaymentData = $this->extractBasePaymentData();

        $creditCardDataIndex = NewVoucherPayment::getBaseCode();
        if (!isset($paymentData[$creditCardDataIndex])) {
            $paymentData[$creditCardDataIndex] = [];
        }
        $paymentData[$creditCardDataIndex][] = $newPaymentData;
    }

    private function extractPaymentDataFromPagarmeDebit(
        $additionalInformation,
        &$paymentData,
        $payment
    ) {
        $newPaymentData = $this->extractBasePaymentData();

        $creditCardDataIndex = NewDebitCardPayment::getBaseCode();
        if (!isset($paymentData[$creditCardDataIndex])) {
            $paymentData[$creditCardDataIndex] = [];
        }
        $paymentData[$creditCardDataIndex][] = $newPaymentData;
    }

    private function extractBasePaymentData()
    {
        $identifier = $this->formData["pagarmetoken1"];

        if (!$identifier) {
            $identifier = $this->formData["card_id"];
        }

        $customerId = $this->getCustomer()->getPagarmeId() ?
            $this->getCustomer()->getPagarmeId()->getValue() : null;
        $brand      = $this->formData["brand"];

        $newPaymentData                  = new stdClass();
        $newPaymentData->customerId      = $customerId;
        $newPaymentData->brand           = $brand;
        $newPaymentData->identifier      = $identifier;
        $newPaymentData->installments    = intval($this->formData["installments"]);
        $newPaymentData->recurrenceCycle = $this->formData["recurrence_cycle"] ?? null;
        $newPaymentData->saveOnSuccess   = isset($this->formData["save_credit_card"]);
        $amount = $this->formData["card_order_value"] ?? $this->getGrandTotal();
        $amount = number_format($amount, 2, '.', '');
        $amount = str_replace('.', '', $amount);
        $amount = str_replace(',', '', $amount);
        $newPaymentData->amount = $amount;

        if (isset($this->formData["enable_multicustomers_card"]) && $this->formData["enable_multicustomers_card"]) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'card'
            );
        }

        return $newPaymentData;
    }

    private function extractPaymentDataFromTwoCreditCards(&$paymentData)
    {
        $moneyService = new MoneyService();
        $indexes      = ['', '2'];
        foreach ($indexes as $index) {
            $identifier = null;
            $customerId = $this->getCustomer()->getPagarmeId() ?
                $this->getCustomer()->getPagarmeId()->getValue() : null;

            $brand = null;
            try {
                $brand = strtolower($this->formData["brand{$index}"]);
            } catch (Throwable $e) {
            }

            $cardTokenFlag = empty($index) ? "pagarmetoken1" : "pagarmetoken2";
            $identifier    = $this->formData[$cardTokenFlag];

            if (empty($identifier) && isset($this->formData["card_id{$index}"])) {
                $identifier = $this->formData["card_id{$index}"];
            }

            $newPaymentData               = new stdClass();
            $newPaymentData->customerId   = $customerId;
            $newPaymentData->identifier   = $identifier;
            $newPaymentData->brand        = $brand;
            $newPaymentData->installments = intval($this->formData["installments{$index}"]);


            $amount = $moneyService->removeSeparators(
                $this->formData["card_order_value{$index}"]
            );

            $newPaymentData->amount = $moneyService->floatToCents($amount / 100);

            $newPaymentData->saveOnSuccess =
                isset($this->formData["save_credit_card{$index}"]);

            $creditCardDataIndex = AbstractCreditCardPayment::getBaseCode();
            if (!isset($paymentData[$creditCardDataIndex])) {
                $paymentData[$creditCardDataIndex] = [];
            }

            $multiCustomerFlag = empty($index) ? "enable_multicustomers_card1"
                : "enable_multicustomers_card2";
            if ($this->formData[$multiCustomerFlag]) {
                $flag = explode("_", $multiCustomerFlag);
                $card = array_pop($flag);

                $newPaymentData->customer = $this->extractMultibuyerData(
                    $card
                );
            }

            $paymentData[$creditCardDataIndex][] = $newPaymentData;
        }
    }

    private function extractMultibuyerData(
        $paymentMethod
    ) {

        if (empty($paymentMethod)) {
            return null;
        }

        $order = new Order($this->getPlatformOrder()->get_id());

        $fields = [
            "multicustomer_{$paymentMethod}[name]"         => "name",
            "multicustomer_{$paymentMethod}[email]"        => "email",
            "multicustomer_{$paymentMethod}[cpf]"          => "document",
            "multicustomer_{$paymentMethod}[street]"       => "street",
            "multicustomer_{$paymentMethod}[number]"       => "number",
            "multicustomer_{$paymentMethod}[neighborhood]" => "neighborhood",
            "multicustomer_{$paymentMethod}[complement]"   => "complement",
            "multicustomer_{$paymentMethod}[city]"         => "city",
            "multicustomer_{$paymentMethod}[state]"        => "state",
            "multicustomer_{$paymentMethod}[zip_code]"     => "zipCode"
        ];

        $multibuyer = new stdClass();

        $phones       = Utils::build_customer_phones_from_order($order);
        $homeNumber   = $phones["home_phone"]["complete_phone"];
        $mobileNumber = $phones["mobile_phone"]["complete_phone"];

        $multibuyer->homePhone   = $homeNumber;
        $multibuyer->mobilePhone = $mobileNumber;

        foreach ($fields as $key => $attribute) {
            $value = $this->formData[$key];

            if ($attribute === 'document' || $attribute === 'zipCode') {
                $value = preg_replace(
                    '/\D/',
                    '',
                    $value
                );
            }

            $multibuyer->$attribute = $value;
        }

        return $multibuyer;
    }

    private function extractPaymentDataFromBilletCreditcard(&$paymentData)
    {
        $moneyService = new MoneyService();
        $identifier   = null;
        $customerId   = $this->getCustomer()->getPagarmeId() ?
            $this->getCustomer()->getPagarmeId()->getValue() : null;

        $brand = null;
        try {
            $brand = strtolower($this->formData["brand"]);
        } catch (Throwable $e) {
        }

        $identifier = $this->formData['pagarmetoken1'];

        if (!$identifier) {
            $identifier = $this->formData["card_id"];
        }

        $newPaymentData               = new stdClass();
        $newPaymentData->identifier   = $identifier;
        $newPaymentData->customerId   = $customerId;
        $newPaymentData->brand        = $brand;
        $newPaymentData->installments = intval($this->formData['installments']);

        $newPaymentData->saveOnSuccess =
            isset($this->formData["save_credit_card"]);

        $amount = str_replace(
            ['.', ','],
            "",
            $this->formData["card_order_value"]
        );

        $newPaymentData->amount = $moneyService->floatToCents($amount / 100);

        $creditCardDataIndex = AbstractCreditCardPayment::getBaseCode();
        if (!isset($paymentData[$creditCardDataIndex])) {
            $paymentData[$creditCardDataIndex] = [];
        }

        if ($this->formData["enable_multicustomers_card"]) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'card'
            );
        }

        $paymentData[$creditCardDataIndex][] = $newPaymentData;

        //boleto

        $newPaymentData = new stdClass();

        $amount = str_replace(
            ['.', ','],
            "",
            $this->formData["billet_value"]
        );

        $newPaymentData->amount =
            $moneyService->floatToCents($amount / 100);

        $boletoDataIndex = BoletoPayment::getBaseCode();
        if (!isset($paymentData[$boletoDataIndex])) {
            $paymentData[$boletoDataIndex] = [];
        }

        if (isset($this->formData["enable_multicustomers_billet"]) && $this->formData["enable_multicustomers_billet"]) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'billet'
            );
        }

        $paymentData[$boletoDataIndex][] = $newPaymentData;
    }

    private function extractPaymentDataFromBillet(&$paymentData)
    {
        $moneyService    = new MoneyService();
        $billetDataIndex = BoletoPayment::getBaseCode();
        $newPaymentData  = (object) $this->paymentInformation[0][$billetDataIndex];

        $amount = isset($this->formData["billet_value"]) ?
            $this->formData["billet_value"] : $this->getGrandTotal();

        $newPaymentData->amount =
            $moneyService->floatToCents($amount);

        $paymentData[$billetDataIndex] = $paymentData[$billetDataIndex] ?? [];

        if (isset($this->formData["enable_multicustomers_billet"]) && $this->formData["enable_multicustomers_billet"]) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'billet'
            );
        }

        $paymentData[$billetDataIndex][] = $newPaymentData;
    }

    private function extractPaymentDataFromPix(&$paymentData)
    {
        $moneyService   = new MoneyService();
        $newPaymentData = new stdClass();

        $amount = $this->getGrandTotal();

        $newPaymentData->amount =
            $moneyService->floatToCents($amount);

        $pixDataIndex = PixPayment::getBaseCode();
        if (!isset($paymentData[$pixDataIndex])) {
            $paymentData[$pixDataIndex] = [];
        }

        if (isset($this->formData["enable_multicustomers_pix"]) && $this->formData["enable_multicustomers_pix"]) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'pix'
            );
        }

        $paymentData[$pixDataIndex][] = $newPaymentData;
    }

    private function extractPaymentDataFromVoucher(&$paymentData)
    {
        $identifier = $this->formData["pagarmetoken1"];
        if (!$identifier) {
            $identifier = $this->formData["card_id"];
        }
        $newPaymentData               = new stdClass();
        $newPaymentData->customerId   = $this->getCustomer()->getPagarmeId() ?
            $this->getCustomer()->getPagarmeId()->getValue() : null;
        $newPaymentData->identifier   = $identifier;
        $newPaymentData->brand        = strtolower($this->formData["brand"]);
        $newPaymentData->installments = (int) 1;
        $amount                       = $this->formData["card_order_value"] ?? $this->getGrandTotal();
        $amount                       = number_format($amount, 2, '.', '');
        $amount                       = str_replace('.', '', $amount);
        $amount                       = str_replace(',', '', $amount);
        $newPaymentData->amount       = $amount;

        if ($this->formData["enable_multicustomers_voucher"]) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'voucher'
            );
        }

        $voucherDataIndex              = NewVoucherPayment::getBaseCode();
        $newPaymentData->saveOnSuccess =
            isset($this->formData["save_credit_card"]);
        if (!isset($paymentData[$voucherDataIndex])) {
            $paymentData[$voucherDataIndex] = [];
        }

        $paymentData[$voucherDataIndex][] = $newPaymentData;

        return $voucherDataIndex;
    }

    public function getShipping()
    {
        $platformShipping = Utils::build_customer_shipping_from_wc_order(
            $this->getPlatformOrder()
        );

        $shipping = new Shipping();

        $shipping->setAmount(
            $platformShipping["amount"]
        );
        $shipping->setDescription($platformShipping["description"]);

        $address = $this->getAddress($platformShipping["address"]);
        $shipping->setAddress($address);

        $customer = $this->getCustomer();
        $shipping->setRecipientName($customer->getName());
        $shipping->setRecipientPhone($customer->getPhones()->getHome());

        return $shipping;
    }

    protected function getAddress($platformAddress)
    {
        $config = new Config();
        if ($config->getAllowNoAddress()) {
            return null;
        }

        $address = new Address();

            $this->validateAddressFields($platformAddress);

        $address->setStreet($platformAddress["street"]);
        $address->setNumber($platformAddress["number"]);
        $address->setNeighborhood($platformAddress["neighborhood"]);
        $address->setComplement($platformAddress["complement"]);

        $address->setCity($platformAddress["city"]);
        $address->setCountry($platformAddress["country"]);
        $address->setZipCode($platformAddress["zip_code"]);

        $address->setState($platformAddress["state"]);

        return $address;
    }

    private function fieldNotSet($requiredField, $platformAddress)
    {
        $fieldIsNotSet = !array_key_exists($requiredField, $platformAddress)
                         || empty($platformAddress[$requiredField]);

        if ($requiredField === 'number') {
            $fieldIsNotSet = !array_key_exists($requiredField, $platformAddress)
                             || (
                                 empty($platformAddress[$requiredField])
                                 && (
                                     $platformAddress[$requiredField] === null
                                     || !is_numeric(trim($platformAddress[$requiredField]))
                                 )
                             );
        }

        return $fieldIsNotSet;
    }

    /**
     * @throws Exception
     */
    private function validateAddressFields($platformAddress)
    {
        $requiredFields = [
            'street',
            'number',
            'neighborhood',
            'city',
            'country',
            'zip_code',
            'state'
        ];

        foreach ($requiredFields as $requiredField) {
            if ($this->fieldNotSet($requiredField, $platformAddress)) {
                $message          = "Missing $requiredField in customer address";
                $ExceptionMessage = $this->i18n->getDashboard($message);
                $exception        = new Exception($ExceptionMessage);
                $log = new LogService('Order', true);
                $log->exception($exception);

                throw $exception;
            }
        }
    }

    public function getTotalCanceled()
    {
        return $this->getPlatformOrder()->get_total_refunded();
    }

    public function handleSplitOrder()
    {
        global $wp_filter;
        if ( !isset($wp_filter['pagarme_split_order'])) {
            return null;
        }

        $order = $this->getPlatformOrder();
        $paymentMethod = $this->getPaymentMethodPlatform();

        $splitDataFromOrder = apply_filters('pagarme_split_order', $order, $paymentMethod);
        $this->validateSellerArray($splitDataFromOrder);
        $splitData = new Split();
        $splitData->setSellersData($splitDataFromOrder['sellers']);
        $splitData->setMarketplaceData($splitDataFromOrder['marketplace']);
        return $splitData;
    }

    private function validateSellerArray($splitDataFromOrder)
    {
        foreach ($splitDataFromOrder['sellers'] as $data) {
            $requiredFields = ['marketplaceCommission', 'commission', 'pagarmeId'];
            foreach ($requiredFields as $field) {
                if (!array_key_exists($field, $data)) {
                    throw new \InvalidArgumentException("The field '$field' is required for each seller.");
                }
            }
        }
    }
}
