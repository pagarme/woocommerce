<?php

namespace Pagarme\Core\Payment\Traits;

use Pagarme\Core\Kernel\Exceptions\InvalidParamException;

//@todo There are many object that should have this same business rule
//      for the amount. Modify all these object to use this trait
//      instead of implementing the behavior for the amount by itself.
trait WithAmountTrait
{
    /** @var int */
    protected $amount;

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @throws InvalidParamException
     */
    public function setAmount($amount)
    {
        if ($amount < 0) {
            throw new InvalidParamException(
                'Amount should be at least 0!',
                $amount
            );
        }
        $this->amount = (int) $amount;
    }
}