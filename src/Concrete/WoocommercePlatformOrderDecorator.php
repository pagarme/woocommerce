<?php

namespace Woocommerce\Pagarme\Concrete;

use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Customer as PagarmeCustomer;
use Woocommerce\Pagarme\Model\Api;
use Woocommerce\Pagarme\Model\Payment as WCModelPayment;
use Woocommerce\Pagarme\Helper\Utils;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as PagarmeSetup;
use Pagarme\Core\Kernel\Abstractions\AbstractPlatformOrderDecorator;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Interfaces\PlatformInvoiceInterface;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Kernel\ValueObjects\OrderState;
use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use Pagarme\Core\Kernel\ValueObjects\PaymentMethod;
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
use Pagarme\Core\Payment\Repositories\SavedCardRepository;
use Pagarme\Core\Payment\ValueObjects\CustomerPhones;
use Pagarme\Core\Payment\ValueObjects\CustomerType;
use Pagarme\Core\Payment\ValueObjects\Phone;
use Pagarme\Core\Recurrence\Services\RecurrenceService;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Kernel\Aggregates\Transaction;
use Pagarme\Core\Kernel\ValueObjects\TransactionType;
use WC_Order;

class WoocommercePlatformOrderDecorator extends AbstractPlatformOrderDecorator
{
    /** @var WC_Order */
    protected $platformOrder;

    private $i18n;
    private $formData;
    private $paymentMethod;

    public function __construct($formData = null, $paymentMethod = null)
    {
        $this->i18n = new LocalizationService();
        $this->formData = $formData;
        $this->paymentMethod = $this->formatPaymentMethod($paymentMethod);
        $this->orderService = new OrderService();
        parent::__construct();
    }

    private function formatPaymentMethod($paymentMethod)
    {
        $paymentMethodParts = explode('_', $paymentMethod);
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
            'pending' => 'stateNew',
            'paid' => 'complete',
            'pending_payment' => 'pending_payment',
            'failed' => 'closed',
            'processing' => 'processing',
            'on_hold' => 'holded',
            'canceled' => 'canceled',
            'refunded' => 'complete',
            'authentication_required' => 'processing'
        ];

        $status = $this->getStatus();

        $state = $statusToState[$status] ?
            $statusToState[$status] : 'processing';

        return OrderState::$state();
    }

    public function setStatusAfterLog(OrderStatus $status)
    {
        $stringStatus = $status->getStatus();
        $this->getPlatformOrder()->set_status($stringStatus);
    }

    public function getStatus()
    {
        return $this->getPlatformOrder()->get_status();
    }

    public function loadByIncrementId($incrementId)
    {
        $wcOrder = new WC_Order($incrementId);
        $this->setPlatformOrder($wcOrder);
    }

    /**
     * @param string $message
     * @return bool
     */
    public function sendEmail($message)
    {
        // we don't have the email functionallity in woocommerce module
        return null;
    }

    /**
     * @param OrderStatus $orderStatus
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
     * @return void
     */
    public function setAdditionalInformation($name, $value)
    {
        return null;
    }

    /**
     * @param Charge[] $charges
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
        return $this->getPlatformOrder()->get_order_number();
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
        $orderId = $this->platformOrder->get_order_number();
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
        $customerId = get_current_user_id();

        if (!empty($customerId)) {
            return $this->getRegisteredCustomer($customerId);
        }

        return $this->getGuestCustomer();
    }

    /**
     * @param PagarmeCustomer $pagarmeCustomer
     * @return Customer
     * @throws \Exception
     */
    private function getRegisteredCustomer($woocommerceCustomerId)
    {
        $order = new Order($this->getPlatformOrder()->get_order_number());

        $address = Utils::build_customer_address_from_order($order);
        $document = Utils::build_document_from_order($order);
        $phones = Utils::build_customer_phones_from_order($order);

        $homeNumber = $phones["home_phone"]["complete_phone"];
        $mobileNumber = $phones["mobile_phone"]["complete_phone"];

        $customerRepository = new CoreCustomerRepository();

        $savedCustomer = $customerRepository->findByCode($woocommerceCustomerId);

        $customer = new Customer;
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

        $homePhone = new Phone($homeNumber);
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
     * @throws \Exception
     */
    private function getGuestCustomer()
    {
        $order = new Order($this->getPlatformOrder()->get_order_number());

        $address = Utils::build_customer_address_from_order($order);
        $document = Utils::build_document_from_order($order);
        $phones = Utils::build_customer_phones_from_order($order);

        $homeNumber = $phones["home_phone"]["complete_phone"];
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

        $homePhone = new Phone($homeNumber);
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
        $moneyService = new MoneyService();
        $itemCollection = $this->getPlatformOrder()->get_items();

        foreach ($itemCollection as $woocommerceItem) {
            //adjusting price.
            $woocommerceProduct = $woocommerceItem->get_product();
            $price = $woocommerceProduct->get_price();
            $price = $price > 0 ? $price : "0.01";

            if ($price === null) {
                continue;
            }

            $item = new Item;
            $item->setAmount(
                $moneyService->floatToCents($price)
            );

            if (!empty($woocommerceProduct->id)) {
                $item->setCode($woocommerceProduct->id);
            }

            $itemQuantity = absint($woocommerceItem['qty']);
            $itemName = sanitize_title($woocommerceItem['name']);

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
            $payment = new WCModelPayment($this->formData['payment_method']);
            $pagarmeCustomer = $this->getCustomer();

            $customer = new \stdClass();
            $customer->id = $pagarmeCustomer->getPagarmeId() ?
                $pagarmeCustomer->getPagarmeId()->getValue() : null;

            $newPayment = $payment->get_payment_data(
                $this->getPlatformOrder(),
                $this->formData,
                $customer
            );

            $payments = $newPayment;
        }

        $paymentData = [];

        foreach ($payments as $payment) {
            $handler = explode('_', $payment['payment_method']);
            array_walk($handler, function (&$part) {
                $part = ucfirst($part);
            });
            $handler = 'extractPaymentDataFrom' . implode('', $handler);
            $this->$handler($paymentData);
        }

        $paymentFactory = new PaymentFactory();
        $paymentMethods = $paymentFactory->createFromJson(
            json_encode($paymentData)
        );
        return $paymentMethods;
    }

    private function extractPaymentDataFromCreditCard(
        &$paymentData
    ) {
        $newPaymentData = $this->extractBasePaymentData();

        $creditCardDataIndex = AbstractCreditCardPayment::getBaseCode();
        if (!isset($paymentData[$creditCardDataIndex])) {
            $paymentData[$creditCardDataIndex] = [];
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
        $brand = $this->formData["brand"];

        $newPaymentData = new \stdClass();
        $newPaymentData->customerId = $customerId;
        $newPaymentData->brand = $brand;
        $newPaymentData->identifier = $identifier;
        $newPaymentData->installments = intval($this->formData["installments"]);
        $newPaymentData->saveOnSuccess =
            isset($this->formData["save_credit_card"]);

        $amount = isset($this->formData["card_order_value"]) ?
            $this->formData["card_order_value"] :
            $this->getGrandTotal() - $this->getBaseTaxAmount();

        $amount = number_format($amount, 2, '.', '');
        $amount = str_replace('.', '', $amount);
        $amount = str_replace(',', '', $amount);

        $newPaymentData->amount = $amount;

        if ($this->formData["enable_multicustomers_card"]) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'card'
            );
        }

        return $newPaymentData;
    }

    private function extractPaymentDataFromPagarmeTwoCreditCard($additionalInformation, &$paymentData, $payment)
    {
        $moneyService = new MoneyService();
        $indexes = ['first', 'second'];
        foreach ($indexes as $index) {
            $identifier = null;
            $customerId = null;

            $brand = null;
            try {
                $brand = strtolower($additionalInformation["cc_type_{$index}"]);
            } catch (\Throwable $e) {
            }

            if (isset($additionalInformation["cc_token_credit_card_{$index}"])) {
                $identifier = $additionalInformation["cc_token_credit_card_{$index}"];
            }

            if (
                !empty($additionalInformation["cc_saved_card_{$index}"]) &&
                $additionalInformation["cc_saved_card_{$index}"] !== null
            ) {
                $identifier = null;
            }

            if ($identifier === null) {
                $objectManager = ObjectManager::getInstance();
                $cardRepo = $objectManager->get(CardsRepository::class);
                $cardId = $additionalInformation["cc_saved_card_{$index}"];
                $card = $cardRepo->getById($cardId);

                $identifier = $card->getCardToken();
                $customerId = $card->getCardId();
            }

            $newPaymentData = new \stdClass();
            $newPaymentData->customerId = $customerId;
            $newPaymentData->identifier = $identifier;
            $newPaymentData->brand = $brand;
            $newPaymentData->installments = $additionalInformation["cc_installments_{$index}"];
            $newPaymentData->customer = $this->extractMultibuyerData(
                'cc',
                $additionalInformation,
                $index
            );

            $amount = $moneyService->removeSeparators(
                $additionalInformation["cc_{$index}_card_amount"]
            );

            $newPaymentData->amount = $moneyService->floatToCents($amount / 100);
            $newPaymentData->saveOnSuccess =
                isset($additionalInformation["cc_savecard_{$index}"]) &&
                $additionalInformation["cc_savecard_{$index}"] === '1';

            $creditCardDataIndex = AbstractCreditCardPayment::getBaseCode();
            if (!isset($paymentData[$creditCardDataIndex])) {
                $paymentData[$creditCardDataIndex] = [];
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

        $order = new Order($this->getPlatformOrder()->get_order_number());

        $fields = [
            "multicustomer_{$paymentMethod}[name]" => "name",
            "multicustomer_{$paymentMethod}[email]" => "email",
            "multicustomer_{$paymentMethod}[cpf]" => "document",
            "multicustomer_{$paymentMethod}[street]" => "street",
            "multicustomer_{$paymentMethod}[number]" => "number",
            "multicustomer_{$paymentMethod}[neighborhood]" => "neighborhood",
            "multicustomer_{$paymentMethod}[complement]" => "complement",
            "multicustomer_{$paymentMethod}[city]" => "city",
            "multicustomer_{$paymentMethod}[state]" => "state",
            "multicustomer_{$paymentMethod}[zip_code]" => "zipCode"
        ];

        $multibuyer = new \stdClass();

        $phones = Utils::build_customer_phones_from_order($order);
        $homeNumber = $phones["home_phone"]["complete_phone"];
        $mobileNumber = $phones["mobile_phone"]["complete_phone"];

        $multibuyer->homePhone = $homeNumber;
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

    private function extractPaymentDataFromPagarmeBilletCreditcard(
        $additionalInformation,
        &$paymentData,
        $payment
    ) {
        $moneyService = new MoneyService();
        $identifier = null;
        $customerId = null;

        $brand = null;
        try {
            $brand = strtolower($additionalInformation['cc_type']);
        } catch (\Throwable $e) {
        }

        if (isset($additionalInformation['cc_token_credit_card'])) {
            $identifier = $additionalInformation['cc_token_credit_card'];
        }

        if (
            !empty($additionalInformation['cc_saved_card']) &&
            $additionalInformation['cc_saved_card'] !== null
        ) {
            $identifier = null;
        }

        if ($identifier === null) {
            $objectManager = ObjectManager::getInstance();
            $cardRepo = $objectManager->get(CardsRepository::class);
            $cardId = $additionalInformation['cc_saved_card'];
            $card = $cardRepo->getById($cardId);

            $identifier = $card->getCardToken();
            $customerId = $card->getCardId();
        }

        $newPaymentData = new \stdClass();
        $newPaymentData->identifier = $identifier;
        $newPaymentData->customerId = $customerId;
        $newPaymentData->brand = $brand;
        $newPaymentData->installments = $additionalInformation['cc_installments'];

        $newPaymentData->saveOnSuccess =
            isset($additionalInformation["cc_savecard"]) &&
            $additionalInformation["cc_savecard"] === '1';

        $amount = str_replace(
            ['.', ','],
            "",
            $additionalInformation["cc_cc_amount"]
        );
        $newPaymentData->amount = $moneyService->floatToCents($amount / 100);

        $creditCardDataIndex = AbstractCreditCardPayment::getBaseCode();
        if (!isset($paymentData[$creditCardDataIndex])) {
            $paymentData[$creditCardDataIndex] = [];
        }

        $newPaymentData->customer = $this->extractMultibuyerData(
            'cc',
            $additionalInformation
        );

        $paymentData[$creditCardDataIndex][] = $newPaymentData;

        //boleto

        $newPaymentData = new \stdClass();

        $amount = str_replace(
            ['.', ','],
            "",
            $additionalInformation["cc_billet_amount"]
        );

        $newPaymentData->amount =
            $moneyService->floatToCents($amount / 100);

        $boletoDataIndex = BoletoPayment::getBaseCode();
        if (!isset($paymentData[$boletoDataIndex])) {
            $paymentData[$boletoDataIndex] = [];
        }

        $newPaymentData->customer = $this->extractMultibuyerData(
            'billet',
            $additionalInformation
        );

        $paymentData[$boletoDataIndex][] = $newPaymentData;
    }

    private function extractPaymentDataFromBoleto(&$paymentData)
    {
        $moneyService = new MoneyService();
        $newPaymentData = new \stdClass();

        $amount = isset($this->formData["billet_value"]) ?
            $this->formData["billet_value"] : $this->getGrandTotal();

        $newPaymentData->amount =
            $moneyService->floatToCents($amount);

        $boletoDataIndex = BoletoPayment::getBaseCode();
        if (!isset($paymentData[$boletoDataIndex])) {
            $paymentData[$boletoDataIndex] = [];
        }

        if ($this->formData["enable_multicustomers_billet"]) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'billet'
            );
        }

        $paymentData[$boletoDataIndex][] = $newPaymentData;
    }

    private function extractPaymentDataFromPagarmePix(
        $additionalInformation,
        &$paymentData,
        $payment
    ) {
        $moneyService = new MoneyService();
        $newPaymentData = new \stdClass();
        $newPaymentData->amount =
            $moneyService->floatToCents($this->platformOrder->getGrandTotal());

        $pixDataIndex = PixPayment::getBaseCode();
        if (!isset($paymentData[$pixDataIndex])) {
            $paymentData[$pixDataIndex] = [];
        }

        if (!empty($additionalInformation['pix_buyer_checkbox'])) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'pix',
                $additionalInformation
            );
        }

        $paymentData[$pixDataIndex][] = $newPaymentData;
    }

    public function getShipping()
    {
        $moneyService = new MoneyService();

        $platformShipping = Utils::build_customer_shipping_from_wc_order(
            $this->getPlatformOrder()
        );

        $shipping = new Shipping();

        $shipping->setAmount(
            $moneyService->floatToCents($platformShipping["amount"])
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
            $fieldIsNotSet = !array_key_exists($requiredField, $platformAddress)
                || empty($platformAddress[$requiredField]);

            if ($fieldIsNotSet) {
                $message = "Missing $requiredField in customer address";
                $ExceptionMessage = $this->i18n->getDashboard($message);
                $exception = new \Exception($ExceptionMessage);

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
}
