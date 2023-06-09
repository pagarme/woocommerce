<?php

namespace Pagarme\Core\Test\Kernel\Services;

use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Services\MoneyService;
use PHPUnit\Framework\TestCase;

class MoneyServiceTests extends TestCase
{
    /**
     * @var MoneyService
     */
    private $moneyService;

    public function setUp(): void
    {
        $this->moneyService = new MoneyService();
    }

    /**
     * @throws InvalidParamException
     */
    public function testCentsToFloat()
    {
        $float = $this->moneyService->centsToFloat(20);
        $this->assertEquals(0.2, $float);
    }

    /**
     * @throws InvalidParamException
     */
    public function testCentsToFloatShouldNotBeInstantiable()
    {
        $amount = 'abc';
        $this->expectException(InvalidParamException::class);
        $this->expectExceptionMessage("Amount should be an integer! Passed value: {$amount}");

        $this->moneyService->centsToFloat($amount);
    }

    public function testFloatToCents()
    {
        $this->assertEquals(1670, $this->moneyService->floatToCents(16.70));
    }
}
