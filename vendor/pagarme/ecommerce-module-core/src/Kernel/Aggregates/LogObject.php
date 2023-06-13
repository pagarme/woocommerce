<?php

namespace Pagarme\Core\Kernel\Aggregates;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\ValueObjects\VersionInfo;

final class LogObject extends AbstractEntity
{
    /**
     *
     * @var VersionInfo
     */
    private $versions;
    private $method;
    private $data;

    public function getPagarmeId()
    {
        $baseObject = new \stdClass();
        $baseObject->versions = $this->getVersions();
        $baseObject->method = $this->getMethod();
        $baseObject->data = $this->getData();

        $json = json_serialize($baseObject);
        $id = hash('sha512', $json);

        return $id;
    }

    /**
     *
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     *
     * @param  mixed $method
     * @return LogObject
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     *
     * @param  mixed $data
     * @return LogObject
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     *
     * @return VersionInfo
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     *
     * @param  $versions
     * @return $this
     */
    public function setVersions($versions)
    {
        $this->versions = $versions;
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
        $baseObject = new \stdClass();

        $baseObject->versions = $this->getVersions();
        $baseObject->method = $this->getMethod();
        $baseObject->data = $this->getData();

        return $baseObject;
    }
}
