<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\DB\Migration;

use Woocommerce\Pagarme\Model\Setting;
use wpdb;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract AbstractMigration
 * @package Woocommerce\Pagarme\DB\Migration
 */
abstract class AbstractMigration
{
    const MIGRATION_SETTINGS = 'migrations';

    /**
     * @var wpdb
     */
    protected $wpdb;

    /**
     * @var Setting
     */
    protected $settings;

    /**
     * @param Setting|null $settings
     */
    public function __construct(
        ?Setting $settings = null
    ) {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->settings = $settings ?? Setting::get_instance();
    }

    /**
     * @param MigrationInterface $migration
     * @return bool
     */
    public function canApply(MigrationInterface $migration): bool
    {
        if ( $this->settings->__get(self::MIGRATION_SETTINGS) &&
            is_array($this->settings->__get(self::MIGRATION_SETTINGS)) &&
            in_array(get_class($migration), $this->settings->__get(self::MIGRATION_SETTINGS)) ) {
            return false;
        }
        return true;
    }

    /**
     * @param MigrationInterface $migration
     * @return void
     */
    public function registerMigration(MigrationInterface $migration)
    {
        $migrationSetting = $this->settings->__get(self::MIGRATION_SETTINGS);
        if (empty($migrationSetting)) {
            $migrationSetting = [];
        }
        $migrationSetting[] = get_class($migration);
        $this->settings->set(self::MIGRATION_SETTINGS, $migrationSetting);
    }

    /**
     * Array of dependent migrations classes
     * @return array
     */
    public function getDependencies(): array
    {
        return [];
    }
}
