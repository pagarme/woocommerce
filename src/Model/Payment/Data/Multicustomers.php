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

use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class Multicustomers
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
class Multicustomers extends AbstractPayment
{
    const FIELD = 'multicustomers';

    /**
     * @param array $data
     * @return bool
     */
    public function isEnable(array $data)
    {
        return array_key_exists('enable', $data) ? $data['enable'] : false;
    }
}
