<?php
/**
 * @author      Open Source Team
 * @copyright   2024 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 * @link        https://pagar.me
 */

declare(strict_types = 1);

namespace Woocommerce\Pagarme\Controller\Checkout;

class CustomerFields
{
    /**
     * @param $fields
     *
     * @return bool
     */
    public function hasDocumentField($fields): bool
    {
        return array_key_exists('billing_cpf', $fields['billing'])
               || array_key_exists('billing_cnpj', $fields['billing']);
    }
}
