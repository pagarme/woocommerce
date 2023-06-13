<?php

namespace Pagarme\Core\Test\Payments\I18N;

use Pagarme\Core\Kernel\I18N\PTBR;
use PHPUnit\Framework\TestCase;

class PTBRTests extends TestCase
{
    /**
     * @var PTBR
     */
    private $ptbr;

    public function setUp(): void
    {
        $this->ptbr = new PTBR();
    }

    public function testInfoTableResultWebHookReceived()
    {
        $this->assertEquals('Webhook recebido: %s %s.%s', $this->ptbr->get('Webhook received: %s %s.%s'));
    }

    public function testInfoTableResulInvoicecanceled()
    {
        $this->assertEquals('Invoice cancelada: #%s', $this->ptbr->get('Invoice canceled: #%s.'));
    }
}
