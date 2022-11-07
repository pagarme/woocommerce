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
 * Class Itau
 * @package Woocommerce\Pagarme\Model\Payment\Billet\Banks
 */
class Itau extends AbstractBank implements BankInterface
{
    /** @var int */
    protected $id = 341;

    /** @var string */
    protected $name = 'Itaú';
}
