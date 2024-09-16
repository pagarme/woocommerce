<?php

namespace Pagarme\Core\Marketplace\Interfaces;

interface RecipientInterface
{
    const REGISTERED = 'registered';

    const VALIDATION_REQUESTED = 'validation_requested';

    const WAITING_FOR_ANALYSIS = 'waiting_for_analysis';

    const ACTIVE = 'active';

    const DISAPPROVED = 'disapproved';

    const SUSPENDED = 'suspended';

    const BLOCKED = 'blocked';

    const INACTIVE = 'inactive';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return RecipientInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getExternalId();

    /**
     * @param string $externalId
     * @return RecipientInterface
     */
    public function setExternalId($externalId);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return RecipientInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     * @return RecipientInterface
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function getDocument();

    /**
     * @param string $document
     * @return RecipientInterface
     */
    public function setDocument($document);

    /**
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * @param mixed $createdAt
     * @return RecipientInterface
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @return mixed
     */
    public function getUpdatedAt();

    /**
     * @param mixed $updatedAt
     * @return RecipientInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt);
}
