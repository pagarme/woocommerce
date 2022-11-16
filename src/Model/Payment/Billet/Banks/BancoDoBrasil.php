<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\Billet\Banks;

use Woocommerce\Pagarme\Model\Payment\Billet\AbstractBank;
use Woocommerce\Pagarme\Model\Payment\Billet\BankInterface;

defined( 'ABSPATH' ) || exit;

/**
 *  Class BancoDoBrasil
 * @package Woocommerce\Pagarme\Model\Payment\Billet\Banks
 */
class BancoDoBrasil extends AbstractBank implements BankInterface
{
    /** @var int */
    protected $id = 001;

    /** @var string */
    protected $name = 'Banco do Brasil';

    /** @var bool */
    protected $prefix = false;
}
