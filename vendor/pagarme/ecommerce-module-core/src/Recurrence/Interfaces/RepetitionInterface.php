<?php

namespace Pagarme\Core\Recurrence\Interfaces;

interface RepetitionInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return \Pagarme\Core\Recurrence\Aggregates\Repetition
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getRecurrencePrice();

    /**
     * @param int $recurrencePrice
     * @return \Pagarme\Core\Recurrence\Aggregates\Repetition
     */
    public function setRecurrencePrice($recurrencePrice);

    /**
     * @return int
     */
    public function getIntervalCount();

    /**
     * @param int $intervalCount
     * @return \Pagarme\Core\Recurrence\Aggregates\Repetition
     */
    public function setIntervalCount($intervalCount);

    /**
     * @return string
     */
    public function getInterval();

    /**
     * @param string $interval
     * @return \Pagarme\Core\Recurrence\Aggregates\Repetition
     */
    public function setInterval($interval);

    /**
     * @return int
     */
    public function getSubscriptionId();

    /**
     * @param int $subscriptionId
     * @return \Pagarme\Core\Recurrence\Aggregates\Repetition
     */
    public function setSubscriptionId($subscriptionId);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param \DateTime $createdAt
     * @return \Pagarme\Core\Recurrence\Aggregates\Repetition
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime $updatedAt
     * @return \Pagarme\Core\Recurrence\Aggregates\Repetition
     */
    public function setUpdatedAt(\DateTime $updatedAt);

    /**
     * @return int
     */
    public function getCycles();

    /**
     * @param int $cycles
     * @return \Pagarme\Core\Recurrence\Aggregates\Repetition
     */
    public function setCycles($cycles);
}