<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\DB\Migration\Migrations;

use Woocommerce\Pagarme\DB\Migration\AbstractMigration;
use Woocommerce\Pagarme\DB\Migration\MigrationInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Class TypeInSavedCardTable
 * @package Woocommerce\Pagarme\DB\Migration\Migrations
 */
class TypeInSavedCardTable extends AbstractMigration implements MigrationInterface
{

    /** @var string */
    const COLUMN_TYPE = 'type';

    /** @var string */
    const TABLE = 'pagarme_module_core_saved_card';

    /**
     * Apply the migrations.
     *
     * @return void
     */
    public function apply(): void
    {
        $table_name = $this->wpdb->prefix . self::TABLE;
        $column_name = self::COLUMN_TYPE;
        $row = $this->wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_name' AND column_name = '$column_name'");
        if (empty($row)) {
            $query = "ALTER TABLE {$table_name} ADD {$column_name} varchar(30) not null comment 'card type' AFTER brand";
            $this->wpdb->query($query);
        }
        $query = "UPDATE {$table_name} SET type = 'credit_card'";
        $this->wpdb->query($query);
    }
}
