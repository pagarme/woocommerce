<?php

namespace Pagarme\Core\Webhook\Aggregates;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Webhook\ValueObjects\WebhookType;

class Webhook extends AbstractEntity
{
    /**
     *
     * @var WebhookType
     */
    protected $type;

    /**
     *
     * @var AbstractEntity
     */
    protected $entity;

    /**
     * @var string
     */
    protected $component;

    /**
     *
     * @return WebhookType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @param  WebhookType $type
     * @return Webhook
     */
    public function setType(WebhookType $type)
    {
        $this->type = $type;
        return $this;
    }

    public function setComponent($data)
    {
        if (
            (isset($data['invoice']) && !empty($data['invoice']))
            || !empty($data['subscription'])
            || $this->type->getEntityType() == 'subscription'
        ) {
            $this->component = 'Recurrence';
            return $this;
        }

        $this->component = 'Kernel';
        return $this;
    }

    public function getComponent()
    {
        return $this->component;
    }

    /**
     *
     * @return AbstractEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     *
     * @param  AbstractEntity $entity
     * @return Webhook
     */
    public function setEntity(AbstractEntity $entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link   https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since  5.4.0
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }
}
