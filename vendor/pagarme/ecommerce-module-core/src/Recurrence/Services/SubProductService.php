<?php

namespace Pagarme\Core\Recurrence\Services;

use Pagarme\Core\Recurrence\Repositories\SubProductRepository;

class SubProductService
{
    public function findByRecurrenceIdAndProductId($recurrenceId, $productId)
    {
        $subProductRepository = $this->getSubProductRepository();
        return $subProductRepository->findByRecurrenceIdAndProductId(
            $recurrenceId,
            $productId
        );
    }

    protected function getSubProductRepository()
    {
        return new SubProductRepository();
    }
}