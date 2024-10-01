<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Block;

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Data\DataObject;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined('ABSPATH') || exit;

/**
 * Class AbstractBlock
 * @package Woocommerce\Pagarme\Block
 */
abstract class AbstractBlock extends DataObject
{
    /** @var string */
    protected $_template = '';

    /** @var Json */
    protected $jsonSerialize;

    /** @var Json */
    protected $scripts;

    /** @var array */
    protected $deps = [];

    /** @var string */
    protected $areaCode = 'front';

    public function __construct(
        Json  $jsonSerialize = null,
        array $data = []
    )
    {
        $this->jsonSerialize = $jsonSerialize ?? new Json;
        parent::__construct($jsonSerialize, $data);
        $this->enqueue_scripts();
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        return $this->_template;
    }

    /**
     * @param string $template
     * @return $this
     */
    protected function setTemplate(string $template)
    {
        $this->_template = $template;
        return $this;
    }

    /**
     * Produce and return block's html output
     * @return string
     */
    public function toHtml()
    {
        return $this->_toHtml();
    }

    /**
     * Override this method in descendants to produce html
     * @return string
     */
    protected function _toHtml()
    {
        return '';
    }

    public function enqueue_scripts($scripts = null, $deps = [])
    {
        if (!$scripts) {
            $scripts = $this->scripts;
        }
        if (is_null($scripts)) {
            return;
        }
        if (!is_array($scripts)) {
            $scripts = [$scripts];
        }

        if (empty($deps)) {
            $deps = $this->deps;
        }
        $defaultDeps = ['jquery', 'jquery.mask'];
        $deps = array_merge($defaultDeps, $deps);

        foreach ($scripts as $script) {
            $fileName = explode('/', $script);
            $id = WCMP_JS_HANDLER_BASE_NAME . end($fileName);
            wp_enqueue_script(
                $id,
                $this->getScriptUrl($script),
                $deps,
                $this->getScriptVer($script), true
            );
        }
    }

    public function getScriptUrl($jsFileName)
    {
        if(filter_var($jsFileName, FILTER_VALIDATE_URL)) {
            return $jsFileName;
        }
        return Core::plugins_url('assets/javascripts/' . $this->areaCode . '/' . $jsFileName . '.js');
    }

    public function getScriptVer($jsFileName)
    {
        if(filter_var($jsFileName, FILTER_VALIDATE_URL)) {
            return date("Ymd");
        }
        return Core::filemtime('assets/javascripts/' . $this->areaCode . '/' . $jsFileName . '.js');
    }
}
