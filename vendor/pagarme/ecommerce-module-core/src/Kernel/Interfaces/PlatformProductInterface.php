<?php

namespace Pagarme\Core\Kernel\Interfaces;

interface PlatformProductInterface
{
    public function getId();
    public function getName();
    public function getDescription();
    public function getType();
    public function getStatus();
    public function getImages();
    public function getPrice();
    public function loadByEntityId($entityId);
    public function decreaseStock($quantity);
}