<?php

namespace Pagarme\Core\Webhook\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

final class WebhookType extends AbstractValueObject
{
    /**
     *
     * @var string
     */
    private $entityType;
    /**
     *
     * @var string
     */
    private $action;

    private function __construct($entityType, $action)
    {
        $this
            ->setEntityType($entityType)
            ->setAction($action);
    }

    static public function fromPostType($postType)
    {
        $data = explode('.', $postType ?? '');
        return new self($data[0], $data[1]);
    }

    /**
     *
     * @param  string $entityType
     * @return WebhookType
     */
    private function setEntityType($entityType)
    {
        $this->entityType = $entityType;
        return $this;
    }

    /**
     *
     * @param  string $action
     * @return WebhookType
     */
    private function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  WebhookType $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->getEntityType() === $object->getEntityType() &&
            $this->getAction() === $object->getAction();
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
        $obj = new \stdClass();

        $obj->entityType = $this->getEntityType();
        $obj->action = $this->getAction();

        return $obj;
    }
}
