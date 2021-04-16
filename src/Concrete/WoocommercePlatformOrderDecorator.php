<?php

namespace Woocommerce\Pagarme\Concrete;

use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Customer as PagarmeCustomer;
use Woocommerce\Pagarme\Model\Api;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
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
use Pagarme\Core\Recurrence\Aggregates\Plan;
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

    private $quote;
    private $i18n;

    /**
     * @var OrderService
     */
    private $orderService;

    public function __construct()
    {
        $this->i18n = new LocalizationService();
        $this->orderService = new OrderService();
        parent::__construct();
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
        // Woocommmerce doesnt have the concept of state, only status;
        return null;
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
        $this->setPlatformOrder(new WC_Order($incrementId));
    }

    /**
     * @param string $message
     * @return bool
     */
    public function sendEmail($message)
    {
        // we didnt have the email functionallity in woocommerce module
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
        throw new \Exception("Not implemented");
    }

    /**
     * @param Charge[] $charges
     * @return array[['key' => value]]
     */
    public function extractAdditionalChargeInformation(array $charges)
    {
        throw new \Exception("Not implemented");
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
        return $this->getPlatformOrder()->getIncrementId();
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
        return $this->getPlatformOrder()->get_payment_method();
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
        // TODO: work-around this one, could be use for other things in core.
        return [];
    }

    /** @return Customer */
    public function getCustomer()
    {
        $customer = new PagarmeCustomer(get_current_user_id());

        if (!empty($customer->customer_id)) {
            return $this->getRegisteredCustomer($customer);
        }

        return $this->getGuestCustomer();
    }

    /**
     * @param PagarmeCustomer $pagarmeCustomer
     * @return Customer
     * @throws \Exception
     */
    private function getRegisteredCustomer($pagarmeCustomer)
    {
        $pagarCustomerId = new CustomerId($pagarmeCustomer->customer_id);

        $order = new Order($this->getPlatformOrder()->get_order_number());
        $api = Api::get_instance();

        $address = $api->build_customer_address_from_order($order);
        $document = $api->get_document_by_person_type($order);
        $phones = $api->get_phones($order);

        $customerRepository = new CoreCustomerRepository();
        try {

            $savedCustomer = $customerRepository->findByPagarmeId($pagarCustomerId);

            $customer = new Customer;
            $customer->setCode($savedCustomer->getId());

            $customer->setPagarmeId($pagarCustomerId);
        } catch (\Throwable $e) {
        }

        if (empty($mpId)) {
            $coreCustomer = $customerRepository->findByCode(
                $savedCustomer->getId()
            );
            if ($coreCustomer !== null) {
                $customer->setPagarmeId($coreCustomer->getPagarmeId());
            }
        }

        $fullName = "{$order->billing_first_name} {$order->billing_last_name}";
        $fullName = substr($fullName, 0, 64);
        $fullName = preg_replace("/  /", " ", $fullName);

        $customer->setName($fullName);
        $customer->setEmail(substr($order->billing_email, 0, 64),);

        $cleanDocument = preg_replace(
            '/\D/',
            '',
            $document
        );

        $customer->setDocument($cleanDocument);
        $customer->setType(CustomerType::individual());

        $telephone = $phones["home_phone"]["number"];
        $phone = new Phone($telephone);

        $customer->setPhones(
            CustomerPhones::create([$phone, $phone])
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
        $guestAddress = $quote->getBillingAddress();

        $customer = new Customer();

        $customer->setName($guestAddress->getName());
        $customer->setEmail($guestAddress->getEmail());

        $cleanDocument = preg_replace(
            '/\D/',
            '',
            $guestAddress->getVatId()
        );

        if (empty($cleanDocument)) {
            $cleanDocument = preg_replace(
                '/\D/',
                '',
                $quote->getCustomerTaxvat()
            );
        }

        $customer->setDocument($cleanDocument);
        $customer->setType(CustomerType::individual());

        $telephone = $guestAddress->getTelephone();
        $phone = new Phone($telephone);

        $customer->setPhones(
            CustomerPhones::create([$phone, $phone])
        );

        $address = $this->getAddress($guestAddress);
        $customer->setAddress($address);

        return $customer;
    }

    /** @return Item[] */
    public function getItemCollection()
    {
        $moneyService = new MoneyService();
        $quote = $this->getQuote();
        $itemCollection = $quote->getItemsCollection();
        $items = [];
        foreach ($itemCollection as $quoteItem) {
            //adjusting price.
            $price = $quoteItem->getPrice();
            $price = $price > 0 ? $price : "0.01";

            if ($price === null) {
                continue;
            }

            /**
             * Bundle product
             */
            if (
                !empty($quoteItem->getParentItemId()) &&
                $quoteItem->getProductType() === 'simple'
            ) {
                continue;
            }

            $item = new Item;
            $item->setAmount(
                $moneyService->floatToCents($price)
            );

            if ($quoteItem->getProductId()) {
                $item->setCode($quoteItem->getProductId());
            }

            $item->setQuantity($quoteItem->getQty());
            $item->setDescription(
                $quoteItem->getName() . ' : ' .
                    $quoteItem->getDescription()
            );

            $item->setName($quoteItem->getName());

            $helper = new RecurrenceProductHelper();
            $selectedRepetition = $helper->getSelectedRepetition($quoteItem);
            $item->setSelectedOption($selectedRepetition);

            $this->setRecurrenceInfo($item, $quoteItem);

            $items[] = $item;
        }
        return $items;
    }

    public function setRecurrenceInfo($item, $quoteItem)
    {
        $recurrenceService = $this->getRecurrenceService();
        $productId = $quoteItem->getProduct()->getId();

        $coreProduct =
            $recurrenceService->getRecurrenceProductByProductId(
                $productId
            );

        if (!$coreProduct) {
            return null;
        }

        $type = $coreProduct->getRecurrenceType();

        if ($type == Plan::RECURRENCE_TYPE) {
            $item->setPagarmeId($coreProduct->getPagarmeId());
            $item->setType($type);
            return $item;
        }

        if (!empty($item->getSelectedOption())) {
            $item->setType($type);
        }

        return $item;
    }

    public function getRecurrenceService()
    {
        return new RecurrenceService();
    }

    public function getQuote()
    {
        if ($this->quote === null) {
            $quoteId = $this->platformOrder->getQuoteId();

            $objectManager = ObjectManager::getInstance();
            $quoteFactory = $objectManager->get(QuoteFactory::class);
            $this->quote = $quoteFactory->create()->load($quoteId);
        }

        return $this->quote;
    }

    /** @return AbstractPayment[] */
    public function getPaymentMethodCollection()
    {
        $payments = $this->getPaymentCollection();

        if (empty($payments)) {
            $baseNewPayment = $this->platformOrder->getPayment();

            $newPayment = [];
            $newPayment['method'] = $baseNewPayment->getMethod();
            $newPayment['additional_information'] =
                $baseNewPayment->getAdditionalInformation();
            $payments = [$newPayment];
        }

        $paymentData = [];

        foreach ($payments as $payment) {
            $handler = explode('_', $payment['method']);
            array_walk($handler, function (&$part) {
                $part = ucfirst($part);
            });
            $handler = 'extractPaymentDataFrom' . implode('', $handler);
            $this->$handler(
                $payment['additional_information'],
                $paymentData,
                $payment
            );
        }

        $paymentFactory = new PaymentFactory();
        $paymentMethods = $paymentFactory->createFromJson(
            json_encode($paymentData)
        );
        return $paymentMethods;
    }

    private function extractPaymentDataFromPagarmeCreditCard(
        $additionalInformation,
        &$paymentData,
        $payment
    ) {
        $newPaymentData = $this->extractBasePaymentData(
            $additionalInformation
        );

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
        $newPaymentData = $this->extractBasePaymentData(
            $additionalInformation
        );

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
        $newPaymentData = $this->extractBasePaymentData(
            $additionalInformation
        );

        $creditCardDataIndex = NewDebitCardPayment::getBaseCode();
        if (!isset($paymentData[$creditCardDataIndex])) {
            $paymentData[$creditCardDataIndex] = [];
        }
        $paymentData[$creditCardDataIndex][] = $newPaymentData;
    }

    private function extractBasePaymentData($additionalInformation)
    {
        $moneyService = new MoneyService();
        $identifier = null;
        $customerId = null;
        $brand = null;

        try {
            $brand = strtolower($additionalInformation['cc_type']);
        } catch (\Exception $e) {
            // do nothing
        } catch (\Throwable $e) {
            // do nothing
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
        $newPaymentData->customerId = $customerId;
        $newPaymentData->brand = $brand;
        $newPaymentData->identifier = $identifier;
        $newPaymentData->installments = $additionalInformation['cc_installments'];
        $newPaymentData->saveOnSuccess =
            isset($additionalInformation['cc_savecard']) &&
            $additionalInformation['cc_savecard'] === '1';

        if (isset($additionalInformation['cc_cvv_card']) && !empty($additionalInformation['cc_cvv_card'])) {
            $newPaymentData->cvvCard = $additionalInformation['cc_cvv_card'];
        }

        $amount = $this->getGrandTotal() - $this->getBaseTaxAmount();
        $amount = number_format($amount, 2, '.', '');
        $amount = str_replace('.', '', $amount);
        $amount = str_replace(',', '', $amount);

        $newPaymentData->amount = $amount;

        if ($additionalInformation['cc_buyer_checkbox']) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'cc',
                $additionalInformation
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
        $prefix,
        $additionalInformation,
        $index = null
    ) {
        $index = $index !== null ? '_' . $index : null;

        if (
            !isset($additionalInformation["{$prefix}_buyer_checkbox{$index}"]) ||
            $additionalInformation["{$prefix}_buyer_checkbox{$index}"] !== "1"
        ) {
            return null;
        }

        $fields = [
            "{$prefix}_buyer_name{$index}" => "name",
            "{$prefix}_buyer_email{$index}" => "email",
            "{$prefix}_buyer_document{$index}" => "document",
            "{$prefix}_buyer_street_title{$index}" => "street",
            "{$prefix}_buyer_street_number{$index}" => "number",
            "{$prefix}_buyer_neighborhood{$index}" => "neighborhood",
            "{$prefix}_buyer_street_complement{$index}" => "complement",
            "{$prefix}_buyer_city{$index}" => "city",
            "{$prefix}_buyer_state{$index}" => "state",
            "{$prefix}_buyer_zipcode{$index}" => "zipCode",
            "{$prefix}_buyer_home_phone{$index}" => "homePhone",
            "{$prefix}_buyer_mobile_phone{$index}" => "mobilePhone"
        ];

        $multibuyer = new \stdClass();

        foreach ($fields as $key => $attribute) {
            $value = $additionalInformation[$key];

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

    private function extractPaymentDataFromPagarmeBillet(
        $additionalInformation,
        &$paymentData,
        $payment
    ) {
        $moneyService = new MoneyService();
        $newPaymentData = new \stdClass();
        $newPaymentData->amount =
            $moneyService->floatToCents($this->platformOrder->getGrandTotal());

        $boletoDataIndex = BoletoPayment::getBaseCode();
        if (!isset($paymentData[$boletoDataIndex])) {
            $paymentData[$boletoDataIndex] = [];
        }

        if ($additionalInformation['billet_buyer_checkbox']) {
            $newPaymentData->customer = $this->extractMultibuyerData(
                'billet',
                $additionalInformation
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
        /** @var Shipping $shipping */
        $shipping = null;
        $quote = $this->getQuote();
        /** @var \Magento\Quote\Model\Quote\Address $platformShipping */
        $platformShipping = $quote->getShippingAddress();

        $shippingMethod = $platformShipping->getShippingMethod();
        if ($shippingMethod === null) { //this is a order without a shipping.
            return null;
        }

        $shipping = new Shipping();

        $shipping->setAmount(
            $moneyService->floatToCents($platformShipping->getShippingAmount())
        );
        $shipping->setDescription($platformShipping->getShippingDescription());
        $shipping->setRecipientName($platformShipping->getName());

        $telephone = $platformShipping->getTelephone();
        $phone = new Phone($telephone);

        $shipping->setRecipientPhone($phone);

        $address = $this->getAddress($platformShipping);
        $shipping->setAddress($address);

        return $shipping;
    }

    protected function getAddress($platformAddress)
    {
        $address = new Address();
        $addressAttributes =
            MPSetup::getModuleConfiguration()->getAddressAttributes();

        $addressAttributes = json_decode(json_encode($addressAttributes), true);
        $allStreetLines = $platformAddress->getStreet();

        $this->validateAddress($allStreetLines);
        $this->validateAddressConfiguration($addressAttributes);

        if (count($allStreetLines) < 4) {
            $addressAttributes['neighborhood'] = "street_3";
            $addressAttributes['complement'] = "street_4";
        }

        foreach ($addressAttributes as $attribute => $value) {
            $value = $value === null ? 1 : $value;

            $street = explode("_", $value);
            if (count($street) > 1) {
                $value = intval($street[1]) - 1;
            }

            $setter = 'set' . ucfirst($attribute);

            if (!isset($allStreetLines[$value])) {
                $address->$setter('');
                continue;
            }

            $address->$setter($platformAddress->getStreet()[$value]);
        }

        $address->setCity($platformAddress->getCity());
        $address->setCountry($platformAddress->getCountryId());
        $address->setZipCode($platformAddress->getPostcode());

        $_regionFactory = ObjectManager::getInstance()->get('Magento\Directory\Model\RegionFactory');
        $regionId = $platformAddress->getRegionId();

        if (is_numeric($regionId)) {
            $shipperRegion = $_regionFactory->create()->load($regionId);
            if ($shipperRegion->getId()) {
                $address->setState($shipperRegion->getCode());
            }
        }

        return $address;
    }

    protected function validateAddress($allStreetLines)
    {
        if (
            !is_array($allStreetLines) ||
            count($allStreetLines) < 3
        ) {
            $message = "Invalid address. Please fill the street lines and try again.";
            $ExceptionMessage = $this->i18n->getDashboard($message);

            $exception = new \Exception($ExceptionMessage);
            $log = new LogService('Order', true);
            $log->exception($exception);

            throw $exception;
        }
    }

    protected function validateAddressConfiguration($addressAttributes)
    {
        $arrayFiltered = array_filter($addressAttributes);
        if (empty($arrayFiltered)) {
            $message = "Invalid address configuration. Please fill the address configuration on admin panel.";
            $ExceptionMessage = $this->i18n->getDashboard($message);
            $exception = new \Exception($ExceptionMessage);

            $log = new LogService('Order', true);
            $log->exception($exception);


            throw $exception;
        }
    }

    public function getTotalCanceled()
    {
        return $this->platformOrder->getTotalCanceled();
    }
}
