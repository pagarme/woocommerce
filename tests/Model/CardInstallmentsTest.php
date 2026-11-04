<?php

namespace Woocommerce\Pagarme\Tests\Model;

use Brain;
use Mockery;
use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Model\CardInstallments;
use Woocommerce\Pagarme\Model\Config;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CardInstallmentsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Brain\Monkey\setUp();
    }

    public function tearDown(): void
    {
        Mockery::close();
        Brain\Monkey\tearDown();
        parent::tearDown();
    }

    private function getOptions($total, $max, $interest, $increase, $noInterest)
    {
        Brain\Monkey\Functions\stubs([
            '__' => fn($text) => $text,
            'wc_price' => fn($price) => (string) $price,
        ]);

        return (new CardInstallments(Mockery::mock(Config::class)))
            ->getOptions($total, $max, 0, $interest, $increase, $noInterest);
    }

    public function testGetOptionsReturnsIntegerCentsWithoutFloatingPointResidue()
    {
        $options = $this->getOptions(77.80, 4, 7, 1.42, 1);

        $this->assertSame(8326, $options[1]['finalPrice']);
        $this->assertSame(4163, $options[1]['installmentPrice']);
        $this->assertSame(8548, $options[3]['finalPrice']);
        $this->assertSame(2137, $options[3]['installmentPrice']);
    }

    public function testTotalWithInterestIsInstallmentTimesTimes()
    {
        $options = $this->getOptions(77.80, 17, 7, 1.42, 1);

        foreach (array_slice($options, 1) as $option) {
            $this->assertSame($option['installmentPrice'] * $option['value'], $option['finalPrice']);
        }
    }

    public function testGetTotalWithInterestReturnsTheDisplayedFinalPrice()
    {
        $installments = Mockery::mock(CardInstallments::class)->makePartial();
        $installments->shouldReceive('getInstallmentsByType')
            ->andReturn($this->getOptions(77.80, 4, 7, 1.42, 1));

        $this->assertSame(7780, $installments->getTotalWithInterest(7780, 1));
        $this->assertSame(8326, $installments->getTotalWithInterest(7780, '2'));
        $this->assertNull($installments->getTotalWithInterest(7780, 10));
    }
}
