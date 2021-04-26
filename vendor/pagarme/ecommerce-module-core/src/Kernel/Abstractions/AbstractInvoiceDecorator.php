<?php

namespace Pagarme\Core\Kernel\Abstractions;

use Pagarme\Core\Kernel\Interfaces\PlatformInvoiceInterface;

abstract class AbstractInvoiceDecorator implements PlatformInvoiceInterface
{
    protected $platformInvoice;

    public function __construct($platformInvoice = null)
    {
        $this->platformInvoice = $platformInvoice;
    }

    public function getPlatformInvoice()
    {
        return $this->platformInvoice;
    }

    /**
     * @since 1.7.2
     */
    public function addComment($comment)
    {
        $comment = 'PGM - ' . $comment;
        $this->addMPComment($comment);
    }

    /**
     * @since 1.7.2
     */
    abstract protected function addMPComment($comment);
}