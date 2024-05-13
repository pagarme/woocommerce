<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model;

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;

defined( 'ABSPATH' ) || exit;

if (!function_exists('add_action')) {
    exit(0);
}

/**
 * Class CardInstallments
 * @package Woocommerce\Pagarme\Model
 */
class CardInstallments
{
    /** @var Config */
    public $config;

    private $subscription;

    const INSTALLMENTS_FOR_ALL_FLAGS = 1;
    const INSTALLMENTS_BY_FLAG = 2;

    /**
     * @param Config|null $config
     */
    public function __construct(
        Config $config = null
    ) {
        if (!$config) {
            $config = new Config();
        }
        $this->config = $config;
        $this->subscription = new Subscription();
    }

    /**
     * @param $total
     * @param $flag
     * @return mixed
     */
    public function getInstallmentsByType($total, $flag = false)
    {
        $total = Utils::str_to_float($total);
        $type = $this->config->getCcInstallmentType() ?? 1;
        $maxInstallments = $this->getMaxCcInstallments($type, $flag);
        $minAmount = Utils::str_to_float($this->config->getCcInstallmentsMinAmount());
        $noInterest = intval($this->config->getCcInstallmentsWithoutInterest());
        $interest = Utils::str_to_float($this->config->getCcInstallmentsInterest());
        $interestIncrease = Utils::str_to_float($this->config->getCcInstallmentsInterestIncrease());
        $method = 'calcInstallments' . $type;
        return $this->{$method}(
            compact('maxInstallments', 'minAmount', 'noInterest', 'interest', 'interestIncrease', 'total', 'flag')
        );
    }

    /**
     * @param $total
     * @param $maxInstallments
     * @param $minAmount
     * @param $interest
     * @param $interestIncrease
     * @param $noInterest
     * @return array
     */
    public function getOptions($total, $maxInstallments, $minAmount, $interest, $interestIncrease, $noInterest)
    {
        $options[] = [
            'value' => 1,
            'content' => __('1x', 'woo-pagarme-payments') . ' (' . wc_price($total) . ')'
        ];
        $interestBase = $interest;
        for ($times = 2; $times <= $maxInstallments; $times++) {
            $interest = $interestBase;
            $amount = $total;
            if ($interest || $interestIncrease) {
                if ($interestIncrease && $times > $noInterest + 1) {
                    $interest += ($interestIncrease * ($times - ($noInterest + 1)));
                }
                $amount += Utils::calc_percentage($interest, $total);
            }
            $value = $amount;
            if ($times <= $noInterest) {
                $value = $total;
            }
            $price = ceil($value / $times * 100) / 100;
            if ($price < $minAmount) {
                break;
            }
            $text  = sprintf(
                __('%dx of %s (%s)', 'woo-pagarme-payments'),
                $times,
                wc_price($price),
                wc_price($value)
            );

            $text .= $this->verifyInterest($times, $noInterest, $interest);

            $options[] = [
                'value' => $times,
                'content' => $text
            ];
        }
        return $options;
    }

    /**
    * @param int $times
    * @param mixed $noInterest
    * @param mixed $interest
    * @return string
    */
    public function verifyInterest(int $times, $noInterest, $interest): string
    {
        if ($times > $noInterest && $interest) {
            return " c/juros";
        }

        return " s/juros";
    }

    /**
     * @param array $options
     * @return string
     */
    public function renderOptions(array $options)
    {
        $html = '';
        if (!$options) {
            $html .= '<option value="">...</option>';
        }
        foreach ($options as $option) {
            $html .= '<option value="' . $option['value'] . '">' . $option['content'] . '</option>';
        }
        return $html;
    }

    /**
     * @param array $params
     * @return array
     */
    private function calcInstallments1(array $params)
    {
        extract($params, EXTR_SKIP);
        return $this->getOptions($total, $maxInstallments, $minAmount, $interest, $interestIncrease, $noInterest);
    }

    /**
     * @param array $params
     * @return array
     */
    private function calcInstallments2(array $params)
    {
        $configByFlags = $this->config->getCcInstallmentsByFlag();
        extract($params, EXTR_SKIP);
        if (!$flag || !isset($configByFlags['max_installment'][$flag])) {
            return [[
                'value' => 0,
                'content' =>  __('This card brand not is allowed on checkout.', Core::SLUG)
            ]];
        }
        $maxInstallments  = $this->getMaxCcInstallments(self::INSTALLMENTS_BY_FLAG, $flag);
        $minAmount = Utils::str_to_float($configByFlags['installment_min_amount'][$flag]);
        $noInterest = intval($configByFlags['no_interest'][$flag]);
        $interest = Utils::str_to_float($configByFlags['interest'][$flag]);
        $interestIncrease = Utils::str_to_float($configByFlags['interest_increase'][$flag]);
        return $this->getOptions($total, $maxInstallments, $minAmount, $interest, $interestIncrease, $noInterest);
    }

    /**
     * @param int $type
     * @param string|bool $flag
     * @return int
     */
    public function getMaxCcInstallments($type, $flag)
    {
        if (
            (Subscription::hasSubscriptionProductInCart() && !$this->subscription->allowInstallments())
            || $this->subscription->hasOneInstallmentPeriodInCart()) {
            return 1;
        }
        if ($type === self::INSTALLMENTS_BY_FLAG) {
            $configByFlags = $this->config->getCcInstallmentsByFlag();
            return intval($configByFlags['max_installment'][$flag]);
        }
        return $this->config->getCcInstallmentsMaximum();
    }
}
