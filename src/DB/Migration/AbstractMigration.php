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

use Woocommerce\Pagarme\Model\Config;
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
     * @var Config
     */
    protected $settings;

    /**
     * @param Config|null $settings
     */
    public function __construct(
        ?Config $settings = null
    ) {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->settings = $settings ?? new Config;
    }

    /**
     * @param MigrationInterface $migration
     * @return bool
     */
    public function canApply(MigrationInterface $migration): bool
    {
        if ( $this->settings->getData(self::MIGRATION_SETTINGS) &&
            is_array($this->settings->getData(self::MIGRATION_SETTINGS)) &&
            in_array(get_class($migration), $this->settings->getData(self::MIGRATION_SETTINGS)) ) {
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
        $migrationSetting = $this->settings->getData(self::MIGRATION_SETTINGS);
        if (!is_array($migrationSetting)) {
            $migrationSetting = [];
        }
        $migrationSetting[] = get_class($migration);
        $this->settings->setData(self::MIGRATION_SETTINGS, $migrationSetting);
        $this->settings->save();
    }

    /**
     * @param MigrationInterface $migration
     * @return void
     */
    public function unregisterMigration(MigrationInterface $migration): void
    {
        $migrationSetting = $this->settings->getData(self::MIGRATION_SETTINGS);
        if (!is_array($migrationSetting)) {
            return;
        }
        $class = get_class($migration);
        $key = array_search($class, $migrationSetting);
        if (is_int($key) && array_key_exists($key,$migrationSetting)) {
            unset($migrationSetting[$key]);
            $migrationSetting = array_values($migrationSetting);
            $this->settings->setData(self::MIGRATION_SETTINGS, $migrationSetting);
        }
        $this->settings->save();
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
