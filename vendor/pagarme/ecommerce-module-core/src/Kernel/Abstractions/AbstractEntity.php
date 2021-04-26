<?php

namespace Pagarme\Core\Kernel\Abstractions;

use JsonSerializable;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

/**
 * The Entity Abstraction. All the aggregate roots that are entities should extend
 * this class.
 *
 * Holds the business rules related to entities.
 */
abstract class AbstractEntity implements JsonSerializable
{
    /**
     *
     * @var int
     */
    protected $id;

    /**
     * Almost every Entity has an equivalent at pagarme. This property holds the
     * Pagarme ID for the entity.
     *
     * @var AbstractValidString
     */
    protected $pagarmeId;

    /**
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param  string $id
     * @return AbstractEntity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     *
     * @return \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     */
    public function getPagarmeId()
    {
        return $this->pagarmeId;
    }

    /**
     *
     * @param  AbstractValidString $pagarmeId
     * @return AbstractEntity
     */
    public function setPagarmeId(AbstractValidString $pagarmeId)
    {
        $this->pagarmeId = $pagarmeId;
        return $this;
    }

    /**
     * Do the identity comparison with another Entity.
     *
     * @param  AbstractEntity $entity
     * @return bool
     */
    public function equals(AbstractEntity $entity)
    {
        return $this->id === $entity->getId();
    }
}