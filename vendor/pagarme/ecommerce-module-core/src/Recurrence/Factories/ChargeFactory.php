<?php

namespace Pagarme\Core\Recurrence\Factories;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Kernel\ValueObjects\Id\InvoiceId;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Core\Payment\Repositories\CustomerRepository;
use Pagarme\Core\Recurrence\Aggregates\Charge;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Factories\TransactionFactory;
use Pagarme\Core\Kernel\Interfaces\FactoryInterface;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use Pagarme\Core\Kernel\ValueObjects\Id\ChargeId;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Payment\Factories\CustomerFactory;
use Pagarme\Core\Kernel\ValueObjects\PaymentMethod;

class ChargeFactory extends TreatFactoryChargeDataBase implements FactoryInterface
{
    /**
     * @var Charge
     */
    private $charge;

    /**
     * ChargeFactory constructor.
     */
    public function __construct()
    {
        $this->charge = new Charge();
    }

    private function setId($id)
    {
        $this->charge->setId($id);
    }

    private function setPagarmeId($id)
    {
        if (empty($id)) {
            return;
        }
        $this->charge->setPagarmeId(new ChargeId($id));
    }

    private function setInvoiceId($postData)
    {
        if (!empty($postData['invoice_id'])) {
            $this->charge->setInvoiceId(new InvoiceId($postData['invoice_id']));
        }
    }

    private function setSubscriptionId($postData)
    {
        if (!empty($postData['subscription_id'])) {
            $this->charge->setSubscriptionId(
                new SubscriptionId($postData['subscription_id'])
            );
        }
    }

    private function setCode($code)
    {
        $this->charge->setCode($code);
    }

    private function setAmount($amount)
    {
        $this->charge->setAmount($amount);
    }

    private function setPaidAmount($postData)
    {
        $paidAmount = isset($postData['paid_amount']) ? $postData['paid_amount'] : 0;
        $this->charge->setPaidAmount($paidAmount);
    }

    private function setPaymentMethod($postData)
    {
        if (!empty($postData['payment_method'])) {
            $paymentMethod = $postData['payment_method'];
            $this->charge->setPaymentMethod(PaymentMethod::{$paymentMethod}());
        }
    }

    private function setStatus($postData)
    {
        if (!empty($postData['status'])) {
            $status = $postData['status'];
            $this->charge->setStatus(ChargeStatus::{$status}());
        }
    }

    private function setCanceledAmount($canceledAmount)
    {
        $this->charge->setCanceledAmount($canceledAmount);
    }

    private function setRefundedAmount($refundedAmount){
        $this->charge->setRefundedAmount($refundedAmount);
    }

    private function setMetadata($data)
    {
        if (!empty($data['metadata'])) {
            $metadata = json_decode(json_encode($data['metadata']));
            $this->charge->setMetadata($metadata);
        }
    }

    private function setCustomer($data)
    {
        if (!empty($data['customer'])) {
            $customerFactory = new CustomerFactory();
            $customer = $customerFactory->createFromPostData($data['customer']);
            $this->charge->setCustomer($customer);
        }
    }

    private function setInvoice($data)
    {
        if (!empty($data['invoice'])) {
            $invoiceFactory = new InvoiceFactory();
            $invoice = $invoiceFactory->createFromPostData($data['invoice']);
            $this->charge->setInvoice($invoice);
            $this->charge->setInvoiceId($invoice->getPagarmeId()->getValue());
        }
    }

    private function setCycleStart($data)
    {
        if (!empty($data['cycle_start'])) {

            if ($data['cycle_start'] instanceOf \DateTime) {
                $this->charge->setCycleStart($data['cycle_start']);
                return $this;
            }

            $this->charge->setCycleStart(new \DateTime($data['cycle_start']));
            return $this;
        }
    }

    private function setCycleEnd($data)
    {
        if (!empty($data['cycle_end'])) {
            if ($data['cycle_start'] instanceOf \DateTime) {
                $this->charge->setCycleEnd($data['cycle_end']);
                return $this;
            }
            $this->charge->setCycleEnd(new \DateTime($data['cycle_end']));
            return $this;
        }
    }

    private function setBoletoLink($data)
    {
        if (!empty($data['boleto_link'])) {
            $this->charge->setBoletoLink($data['boleto_link']);
        }
    }

    private function setBoletoUrl($data)
    {
        if (!empty($data['boleto_link'])) {
            $this->charge->setBoletoUrl($data['boleto_link']);
        }

        if (
            !empty($data['last_transaction']) &&
            !empty($data['last_transaction']['url'])
        ) {
            $this->charge->setBoletoUrl($data['last_transaction']['url']);
        }
    }

    /**
     * @param $postData
     * @return mixed
     * @throws InvalidParamException
     */
    private function addTransaction($postData)
    {
        $lastTransactionData = null;
        if (isset($postData['last_transaction'])) {
            $lastTransactionData = $postData['last_transaction'];
        }

        if ($lastTransactionData !== null) {
            $transactionFactory = new TransactionFactory();
            $lastTransaction = $transactionFactory->createFromPostData($lastTransactionData);
            $lastTransaction->setChargeId($this->charge->getPagarmeId());

            $this->charge->addTransaction($lastTransaction);
        }
    }

    public function createFromPostData($postData)
    {
        $this->setPagarmeId($postData['id']);
        $this->setCode($postData['code']);
        $this->setAmount($postData['amount']);
        $this->setPaidAmount($postData);
        $this->setPaymentMethod($postData);
        $this->addTransaction($postData);
        $this->setStatus($postData);
        $this->setCustomer($postData);
        $this->setInvoice($postData);
        $this->setCycleStart($postData);
        $this->setCycleEnd($postData);
        $this->setBoletoUrl($postData);
        $this->setMetadata($postData);
        $this->setSubscriptionId($postData);
        $this->setInvoiceId($postData);

        return $this->charge;
    }

    /**
     * @param array $dbData
     * @return AbstractEntity|Charge
     * @throws InvalidParamException
     */
    public function createFromDbData($dbData)
    {
        $this->setId($dbData['id']);
        $this->setPagarmeId($dbData['pagarme_id']);
        $this->setInvoice($dbData);
        $this->setSubscriptionId($dbData);
        $this->setInvoiceId($dbData);
        $this->setCode($dbData['code']);
        $this->setAmount($dbData['amount']);
        $this->charge->setPaidAmount(intval($dbData['paid_amount']));
        $this->setCanceledAmount($dbData['canceled_amount']);
        $this->setRefundedAmount($dbData['refunded_amount']);
        $this->setStatus($dbData);
        $this->setBoletoLink($dbData);
        $this->setBoletoUrl($dbData); /** @todo Fixme **/
        $this->setCustomer($dbData);
        $this->setPaymentMethod($dbData);
        $this->setCycleStart($dbData);
        $this->setCycleEnd($dbData);
        $this->setInvoice($dbData);
        $this->setMetadata($dbData);

        return $this->charge;
    }
}