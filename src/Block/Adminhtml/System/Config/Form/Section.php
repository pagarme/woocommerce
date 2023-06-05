<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form;

defined( 'ABSPATH' ) || exit;

/**
 * Class Section
 * @package Woocommerce\Pagarme\Block\Adminthml\System\Config\Form\Field
 */
class Section
{
    /** @var string */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $page;

    /**
     * @param array $data
     */
    public function __construct(
        array $data = []
    ) {
        $this->init($data);
    }

    /**
     * @return void
     */
    protected function init(array $data = [])
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }
    }

    /**
     * @return void
     */
    public function toHtml()
    {
        add_settings_section(
            $this->getId(),
            __($this->getTitle(), 'woo-pagarme-payments'),
            [$this, 'getSectionCallBack'],
            $this->getPage()
        );
    }

    /**
     * @return void
     */
    public function getSectionCallBack($values)
    {
        if (!method_exists($this, 'sectionCallBack')) {
            $this->emptyCallBack();
            return;
        }
        $this->sectionCallBack($values);
    }

    /**
     * Null fallback.
     */
    public function emptyCallBack()
    {
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $page
     * @return $this
     */
    public function setPage(string $page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }
}
