<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Core\Kernel\Log;

/**
 * Class BlurData
 */
class BlurData
{
    /** @var string */
    const TOTAL_BLUR = '***********';

    /**
     * @param string $method
     * @return string
     */
    public function getBlurMethod(string $method)
    {
        return 'blur' . str_replace(' ', '', ucwords(str_replace('_', ' ', $method ?? '')));
    }

    /**
     * @param string $value
     * @param int $delimiter
     * @return string
     */
    private function blurStringSensitiveData($value, $delimiter)
    {
        if (empty($value)) {
            return '';
        }
        $displayed = mb_substr($value, 0, $delimiter);
        $blur = str_repeat("*", mb_strlen($value) - $delimiter);
        return $displayed . $blur;
    }

    /**
     * @param $string
     * @return string
     */
    private function blurEmailSensitiveData($string)
    {
        $displayed = mb_substr($string, 0, 3);
        $final = mb_substr($string, mb_strpos($string, "@"));
        $result = "$displayed***$final";
        return $result;
    }

    /**
     * @param string $name
     * @return string
     */
    public function blurName(string $name)
    {
        return $this->blurStringSensitiveData($name, 5);
    }

    /**
     * @param $billingAddress
     * @return array|mixed
     */
    public function blurBillingAddress($billingAddress)
    {
        return $this->blurAddress($billingAddress);
    }

    /**
     * @param string $email
     * @return string
     */
    public function blurEmail(string $email)
    {
        return $this->blurEmailSensitiveData($email);
    }

    /**
     * @param string $street
     * @return string
     */
    public function blurStreet(string $street)
    {
        return $this->blurStringSensitiveData($street, 8);
    }

    /**
     * @param string $document
     * @return string
     */
    public function blurDocument(string $document)
    {
        return preg_replace('/\B[^@.]/', '*', $document ?? '');
    }

    /**
     * @param string|null $line1
     * @return string
     */
    public function blurLine1(?string $line1)
    {
        return $this->blurStringSensitiveData($line1, 8);
    }

    /**
     * @param string|null $line2
     * @return string
     */
    public function blurLine2(?string $line2)
    {
        return self::TOTAL_BLUR;
    }

    /**
     * @param string|null $complement
     * @return string
     */
    public function blurComplement(?string $complement)
    {
        return self::TOTAL_BLUR;
    }

    /**
     * @param string|null $city
     * @return string
     */
    public function blurCity(?string $city)
    {
        return self::TOTAL_BLUR;
    }

    /**
     * @param string|null $state
     * @return string
     */
    public function blurState(?string $state)
    {
        return self::TOTAL_BLUR;
    }

    /**
     * @param string|null $country
     * @return string
     */
    public function blurCountry(?string $country)
    {
        return self::TOTAL_BLUR;
    }

    /**
     * @param string|null $number
     * @return string
     */
    public function blurNumber(?string $number)
    {
        return self::TOTAL_BLUR;
    }

    /**
     * @param string|null $neighborhood
     * @return string
     */
    public function blurNeighborhood(?string $neighborhood)
    {
        return self::TOTAL_BLUR;
    }

    /**
     * @param string|null $holderName
     * @return string
     */
    public function blurHolderName(?string $holderName)
    {
        $holderName = $holderName ?? "";
        return preg_replace('/^.{8}/', '$1**', $holderName ?? '');
    }

    /**
     * @param string|null $zipCode
     * @return string
     */
    public function blurZipCode(?string $zipCode)
    {
        return $this->blurStringSensitiveData($zipCode, 5);
    }

    /**
     * @param string|null $recipientName
     * @return string
     */
    public function blurRecipientName(?string $recipientName)
    {
        return $this->blurStringSensitiveData($recipientName, 5);
    }

    /**
     * @param string|null $recipientPhone
     * @return string
     */
    public function blurRecipientPhone(?string $recipientPhone)
    {
        return $this->blurStringSensitiveData($recipientPhone, 5);
    }

    /**
     * @param array $data
     * @return array
     */
    public function blurArrayData(array $data)
    {
        foreach ($data as $key => $value) {
            $blurMethod = $this->getBlurMethod($key);
            if (method_exists($this, $blurMethod)) {
                $data[$key] = $this->{$blurMethod}($value);
            }
        }
        return $data;
    }

    public function blurObjectData($data)
    {
        foreach (get_object_vars($data) as $var => $value) {
            $blurMethod = $this->getBlurMethod($var);
            if (method_exists($this, $blurMethod)) {
                $data->{$var} = $this->{$blurMethod}($value);
            }
        }
        return $data;
    }

    /**
     * @param $data
     * @return array|mixed
     */
    public function blurData($data)
    {
        switch ($data) {
            case null:
                break;
            case is_array($data):
                $data = $this->blurArrayData($data);
                break;
            case is_object($data):
                $data = $this->blurObjectData($data);
                break;
        }
        return $data;
    }

    /**
     * @param $customer
     * @return mixed
     */
    public function blurCustomer($customer)
    {
        return $this->blurData($customer);
    }

    /**
     * @param $address
     * @return mixed
     */
    public function blurAddress($address)
    {
        return $this->blurData($address);
    }

    /**
     * @param $shipping
     * @return mixed
     */
    public function blurShipping($shipping)
    {
        return $this->blurData($shipping);
    }

    /**
     * @param $payments
     * @return mixed
     */
    public function blurPayments($payments)
    {
        foreach ($payments as &$payment) {
            $payment = $this->blurData($payment);
        }
        return $payments;
    }

    /**
     * @param $creditCard
     * @return mixed
     */
    public function blurCreditCard($creditCard)
    {
        return $this->blurData($creditCard);
    }

    /**
     * @param $lastTransaction
     * @return mixed
     */
    public function blurLastTransaction($lastTransaction)
    {
        return $this->blurData($lastTransaction);
    }

    /**
     * @param $card
     * @return mixed
     */
    public function blurCard($card)
    {
        return $this->blurData($card);
    }

    /**
     * @param $charges
     * @return mixed
     */
    public function blurCharges($charges)
    {
        foreach ($charges as &$charge) {
            $charge = $this->blurData($charge);
        }
        return $charges;
    }
}
