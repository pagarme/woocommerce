<?php

namespace Pagarme\Core\Recurrence\Aggregates;

use DateTime;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\ValueObjects\Id\CycleId;

class Cycle extends AbstractEntity
{
    /**
     * @var CycleId
     */
    private $cycleId;

    /**
     * @var Datetime
     */
    private $cycleStart;

    /**
     * @var Datetime
     */
    private $cycleEnd;

    /**
     * @var int
     */
    private $cycle;

    public function getCycle()
    {
        return $this->cycle;
    }

    public function setCycle($cycle)
    {
        $this->cycle = $cycle;
        return $this;
    }

    /**
     * @return CycleId
     */
    public function getCycleId()
    {
        return $this->cycleId;
    }

    /**
     *
     * @param  CycleId $cycleId
     * @return $this
     */
    public function setCycleId(CycleId $cycleId)
    {
        $this->cycleId = $cycleId;
        return $this;
    }

    /**
     * @param DateTime $cycleStart
     * @return $this
     */
    public function setCycleStart(DateTime $cycleStart)
    {
        $this->cycleStart = $cycleStart;
        return $this;
    }

    /**
     * @return null|DateTime
     */
    public function getCycleStart()
    {
        return $this->cycleStart;
    }

    /**
     * @param DateTime $cycleEnd
     * @return $this
     */
    public function setCycleEnd(DateTime $cycleEnd)
    {
        $this->cycleEnd = $cycleEnd;
        return $this;
    }

    /**
     * @return null|DateTime
     */
    public function getCycleEnd()
    {
        return $this->cycleEnd;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
