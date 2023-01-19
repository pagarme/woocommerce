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
    /** @var  */
    private $config;

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
    }

    /**
     * @param $total
     * @param $flag
     * @return mixed
     */
    public function getInstallmentsByType($total, $flag = false)
    {
        $type = $this->config->getCcInstallmentType() ?? 1;
        $maxInstallments = $this->config->getCcInstallmentsMaximum();
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
        $interest_base = $interest;
        for ($times = 2; $times <= $maxInstallments; $times++) {
            $interest = $interest_base;
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
            $amount = $total;
            if ($times > $noInterest && $interest) {
                $text .= " c/juros de {$interest}%";
            }
            $options[] = [
                'value' => $times,
                'content' => $text
            ];
        }
        return $options;
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
     * @return string
     */
    private function calcInstallments1(array $params)
    {
        extract($params, EXTR_SKIP);
        return $this->getOptions($total, $maxInstallments, $minAmount, $interest, $interestIncrease, $noInterest);
    }

    /**
     * @param array $params
     * @return string
     */
    private function calcInstallments2(array $params)
    {
        $configByFlags = $this->config->getCcInstallmentsByFlag();
        extract($params, EXTR_SKIP);
        if (!$flag || !isset($configByFlags['max_installment'][$flag])) {
            return sprintf('<option value="">%s</option>', __('This card brand not is allowed on checkout.', Core::SLUG));
        }
        $maxInstallments  = intval($configByFlags['max_installment'][$flag]);
        $minAmount = Utils::str_to_float($configByFlags['installment_min_amount'][$flag]);
        $noInterest = intval($configByFlags['no_interest'][$flag]);
        $interest = Utils::str_to_float($configByFlags['interest'][$flag]);
        $interestIncrease = Utils::str_to_float($configByFlags['interest_increase'][$flag]);
        return $this->getOptions($total, $maxInstallments, $minAmount, $interest, $interestIncrease, $noInterest);
    }


}
