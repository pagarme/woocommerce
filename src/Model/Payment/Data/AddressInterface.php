<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Interface AddressInterface
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
interface AddressInterface
{
    /** @var string */
    const FIRST_NAME = 'first_name';

    /** @var string */
    const LAST_NAME = 'last_name';

    /** @var string */
    const COMPANY = 'company';

    /** @var string */
    const COUNTRY = 'country';

    /** @var string */
    const POSTCODE = 'postcode';

    /** @var string */
    const ADDRESS_1 = 'address_1';

    /** @var string */
    const NUMBER = 'number';

    /** @var string */
    const ADDRESS_2 = 'address_2';

    /** @var string */
    const NEIGHBORHOOD = 'neighborhood';

    /** @var string */
    const CITY = 'city';

    /** @var string */
    const STATE = 'state';

    /** @var string */
    const PERSONTYPE = 'persontype';

    /** @var string */
    const CPF = 'cpf';

    /** @var string */
    const CNPJ = 'cnpj';

    /** @var string */
    const PHONE = 'phone';

    /** @var string */
    const CELLPHONE = 'cellphone';

    /** @var string */
    const EMAIL = 'email';

    /**
     * @return $this
     */
    public function get();
}
