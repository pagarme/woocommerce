<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block;

use Woocommerce\Pagarme\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Template
 * @package Woocommerce\Pagarme\Block
 */
class Template extends AbstractBlock
{
    /**
     * Create block instance
     * @param string|AbstractBlock $block
     * @param string $name
     * @param array $arguments
     * @return AbstractBlock
     * @throws \Exception
     */
    public function createBlock($block, $name, array $arguments = [])
    {
        $block = $this->getBlockInstance($block, $arguments);
        $block->setType(get_class($block));
        $block->setNameInLayout($name);
        $block->addData($arguments['data'] ?? []);
        if (!empty($arguments['template'])) {
            $block->setTemplate($arguments['template']);
        }
        return $block;
    }

    /**
     * Create block object instance based on block type
     * @param string|AbstractBlock $block
     * @param array $arguments
     * @return AbstractBlock
     *@throws \Exception
     */
    private function getBlockInstance($block, array $arguments = [])
    {
        if ($block && is_string($block)) {
            $block = new $block($this->jsonSerialize, $arguments);
        }
        if (!$block instanceof AbstractBlock) {
            throw new \Exception('Invalid block type: ' . is_object($block) ? get_class($block) : (string) $block);
        }
        return $block;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $locale = Core::plugin_dir_path() . $this->getTemplate() . '.php';
        if (!file_exists($locale)) {
            $locale = Core::plugin_dir_path() . $this->getTemplate() . '.phtml';
            if (!file_exists($locale)) {
                return;
            }
        }
        include $locale;
    }
}
