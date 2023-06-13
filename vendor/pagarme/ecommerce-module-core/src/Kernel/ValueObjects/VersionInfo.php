<?php

namespace Pagarme\Core\Kernel\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

final class VersionInfo extends AbstractValueObject
{
    /**
     *
     * @var string
     */
    private $moduleVersion;
    /**
     *
     * @var string
     */
    private $coreVersion;

    /**
     * @var string
     */
    private $platformVersion;

    public function __construct($moduleVersion, $coreVersion, $platformVersion)
    {
        $this->setModuleVersion($moduleVersion);
        $this->setCoreVersion($coreVersion);
        $this->setPlatformVersion($platformVersion);
    }

    /**
     *
     * @return string
     */
    public function getModuleVersion()
    {
        return $this->moduleVersion;
    }

    /**
     *
     * @param  string $moduleVersion
     * @return VersionInfo
     */
    private function setModuleVersion($moduleVersion)
    {
        $this->moduleVersion = $moduleVersion;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCoreVersion()
    {
        return $this->coreVersion;
    }

    /**
     *
     * @param  string $coreVersion
     * @return VersionInfo
     */
    private function setCoreVersion($coreVersion)
    {
        $this->coreVersion = $coreVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlatformVersion()
    {
        return $this->platformVersion;
    }

    /**
     * @param string $platformVersion
     */
    private function setPlatformVersion($platformVersion)
    {
        $this->platformVersion = $platformVersion;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  VersionInfo $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->getCoreVersion() === $object->getCoreVersion() &&
            $this->getModuleVersion() === $object->getModuleVersion() &&
            $this->getPlatformVersion() === $object->getPlatformVersion()
            ;
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

        $obj->moduleVersion = $this->getModuleVersion();
        $obj->coreVersion = $this->getCoreVersion();
        $obj->platformVersion = $this->getPlatformVersion();

        return $obj ;
    }
}
