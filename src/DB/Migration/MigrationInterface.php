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

defined( 'ABSPATH' ) || exit;

/**
 * Interface MigrationInterface
 * @package Woocommerce\Pagarme\DB\Migration
 */
interface MigrationInterface
{
    /**
     * Apply the migrations.
     * @return void
     */
    public function apply(): void;

    /**
     * Verify that migrations can be applied
     * @param MigrationInterface $migration
     * @return bool
     */
    public function canApply(MigrationInterface $migration): bool;

    /**
     * Log migrations already executed
     * @param MigrationInterface $migration
     * @return void
     */
    public function registerMigration(MigrationInterface $migration);

    /**
     * Log migrations already executed
     * @return array
     */
    public function getDependencies(): array;

    /**
     * @param MigrationInterface $migration
     * @return void
     */
    public function unregisterMigration(MigrationInterface $migration): void;

    /**
     * Checks if the migration has already been run. Returns true if not.
     * @return bool
     */
    public function validate(): bool;
}
