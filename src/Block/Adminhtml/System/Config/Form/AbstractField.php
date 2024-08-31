<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 * @link        https://pagar.me
 */

declare(strict_types = 1);

namespace Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form;

use Woocommerce\Pagarme\Model\Config;

defined('ABSPATH') || exit;

/**
 * Abstract Field
 * @package Woocommerce\Pagarme\Block\Adminthml\System\Config\Form\Field
 */
abstract class AbstractField
{
    /** @var string */
    protected $template = 'main.phtml';

    /** @var string */
    protected $templatePath = 'templates/adminhtml/system/config/form/field/';

    /** @var mixed|null */
    protected $default = null;

    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string|null */
    private $current = null;

    /** @var ?string */
    private $description = null;

    /** @var string */
    private $page;

    /** @var string */
    private $title;

    /** @var string */
    private $section;

    /** @var Config */
    protected $config;

    /** @var bool */
    protected $isVisible = true;

    /**
     * @param Config|null $config
     * @param string $template
     * @param array $data
     */
    public function __construct(
        Config $config = null,
        string $template = '',
        array $data = []
    ) {
        $this->config = $config;
        if (!$this->config) {
            $this->config = new Config();
        }
        if ($template) {
            $this->template = $template;
        }
        $this->init($data);
    }

    /**
     * @return void
     */
    protected function init(array $data = [])
    {
        $this->setData($data);
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data = [])
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }

        return $this;
    }

    /**
     * @return void
     */
    public function toHtml()
    {
        add_settings_field(
            $this->getId(),
            __($this->getTitle(), 'woo-pagarme-payments'),
            [$this, 'getElementCallBack'],
            $this->getPage(),
            $this->getSection(),
            json_encode($this)
        );
    }

    /**
     * @return void
     */
    public function getElementCallBack($values)
    {
        if (!method_exists($this, 'elementCallBack')) {
            $this->includeTemplate();

            return;
        }
        $this->elementCallBack($values);
    }

    /**
     * @return void
     */
    public function includeTemplate(string $file = 'main.phtml')
    {
        include plugin_dir_path(WCMP_ROOT_SRC) . DIRECTORY_SEPARATOR . $this->templatePath . $file;
    }

    /**
     * @param string $section
     *
     * @return $this
     */
    public function setSection(string $section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param string $id
     *
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
     *
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

    /**
     * @param string $title
     *
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
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $current
     *
     * @return $this
     */
    public function setCurrent($current)
    {
        $this->current = $current;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrent()
    {
        if (!$this->current) {
            return $this->getDefault();
        }

        return $this->current;
    }

    /**
     * @param $default
     *
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        if (is_array($description)) {
            $this->description = vsprintf(
                __($description['format'], 'woo-pagarme-payments'),
                $description['values']
            );

            return $this;
        }
        $this->description = __($description, 'woo-pagarme-payments');

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function clearData($key = null)
    {
        if (!$key) {
            foreach (get_class_methods(get_class($this)) as $method) {
                if (strpos($method, 'set') !== false) {
                    $this->{$method} = '';
                }
            }
        }
        if ($key) {
            $this->{$key} = '';
        }

        return $this;
    }
}
