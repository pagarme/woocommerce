<?php

namespace Pagarme\Core\Recurrence\Factories;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Interfaces\FactoryInterface;
use Pagarme\Core\Recurrence\Aggregates\Cycle;
use Pagarme\Core\Kernel\ValueObjects\Id\CycleId;

class CycleFactory implements FactoryInterface
{
    private $cycle;

    public function __construct()
    {
        $this->cycle = new Cycle();
    }

    /**
     * @param array $postData
     * @return AbstractEntity|Cycle
     * @throws \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function createFromPostData($postData)
    {
        $this->cycle->setCycleId(new CycleId($postData['id']));
        $this->cycle->setCycleStart(new \DateTime($postData['start_at']));
        $this->cycle->setCycleEnd(new \DateTime($postData['end_at']));
        $this->setCycle($postData);

        return $this->cycle;
    }

    public function createFromDbData($dbData)
    {
        $cycle = new Cycle();

        $cycle->setCycleId(new CycleId($dbData['id']));
        $cycle->setCycleStart(new \DateTime($dbData['start_at']));
        $cycle->setCycleEnd(new \DateTime($dbData['end_at']));

        return $cycle;
    }

    public function setCycle($postData)
    {
        if (!empty($postData['cycle'])) {
            $this->cycle->setCycle($postData['cycle']);
        }
    }
}
