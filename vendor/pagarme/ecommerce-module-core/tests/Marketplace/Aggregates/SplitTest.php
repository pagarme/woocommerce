<?php

use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Marketplace\Aggregates\Split;
use Pagarme\Core\Kernel\ValueObjects\Id\RecipientId;
use PHPUnit\Framework\TestCase;

class SplitTest extends TestCase
{
    private $split;

    protected function setUp(): void
    {
        $this->split = new Split();
    }

    public function testSplitObject()
    {
        $this->split->setRecipientId(new RecipientId('rp_1234567890123457'));
        $this->split->setCommission(10);


        $this->assertEquals(10, $this->split->getCommission());
        $this->assertEquals('rp_1234567890123457', $this->split->getRecipientId());
    }

    public function testSplitShouldBeCreated()
    {
        $split = new Split();
        $this->assertTrue($split !== null);
    }

    /**
     * @throws InvalidParamException
     * @expectedExceptionMessage Commission should be greater or equal to 0! Passed value: -10
     * @expectedExceptionCode 400
     */
    public function testShouldThrowAnExceptionIfCommissionIsInvalid()
    {
        $this->expectException(InvalidParamException::class);

        $this->split->setCommission(-10);
    }

    public function testExpectedAnObjectRecipientIdToSetRecipientId()
    {
        $recipientId = Mockery::mock(RecipientId::class);
        $this->split->setRecipientId($recipientId);

        $this->assertEquals($recipientId, $this->split->getRecipientId());
        $this->assertInstanceOf(RecipientId::class, $this->split->getRecipientId());
    }
}
