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

use ReflectionClass;
use Woocommerce\Pagarme\DB\Migration\Migrations\MigrationVersion207;

defined( 'ABSPATH' ) || exit;

/**
 * Class Migrator
 * @package Woocommerce\Pagarme\DB\Migration
 */
class Migrator
{
    /**
     * @throws \ReflectionException
     */
    public function execute()
    {
        $this->autoLoad();
        $migrationsClasses = $this->getMigrations();
        $this->sort($migrationsClasses);
        if (count($migrationsClasses)) {
            foreach ($migrationsClasses as $class) {
                /** @var MigrationInterface $migration */
                $migration = new $class;
                if ($migration->validate()) {
                    $migration->unregisterMigration($migration);
                }
                if ($migration->canApply($migration)) {
                    $migration->apply();
                    $migration->registerMigration($migration);
                }
            }
        }
    }

    public function autoLoad()
    {
        foreach(glob( __DIR__ . '/Migrations/*.php') as $file) {
            include_once($file);
        }
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function getMigrations()
    {
        $classes = get_declared_classes();
        $implements = [];
        foreach($classes as $klass) {
            $reflect = new ReflectionClass($klass);
            if($reflect->implementsInterface(MigrationInterface::class)) {
                $explodedFileName = explode(DIRECTORY_SEPARATOR, $reflect->getFileName());
                $implements[end($explodedFileName)] = $klass;
            }
        }
        return $implements;
    }

    /**
     * @param $migrationsClasses
     * @return void
     */
    private function sort(&$migrationsClasses)
    {
        if (is_array($migrationsClasses)) {
            ksort($migrationsClasses);
        }
    }
}
