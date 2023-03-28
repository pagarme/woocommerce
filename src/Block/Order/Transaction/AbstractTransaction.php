<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Block\Order\Transaction;

use Woocommerce\Pagarme\Block\Template;

defined('ABSPATH') || exit;

/**
 * Class AbstractTransaction
 * @package Woocommerce\Pagarme\Block
 */
abstract class AbstractTransaction extends Template
{
    /**
     * @param string $path
     * @return string
     */
    public function getFilePath(string $path)
    {
        return esc_url(plugins_url($path, WCMP_ROOT_FILE));
    }

    /**
     * @return array|string|string[]|void
     */
    public function getTransactionType()
    {
        if ($this->getTransaction() && $this->getTransaction()->getTransactionType()->getType()) {
            return str_replace(' ', '', ucwords(str_replace('_', ' ', $this->getTransaction()->getTransactionType()->getType())));
        }
    }
}
