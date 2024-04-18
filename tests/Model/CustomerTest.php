<?php

namespace Woocommerce\Pagarme\Tests\Model;

use Brain;
use Mockery;
use stdClass;
use ReflectionClass;
use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Model\Customer;
use Pagarme\Core\Payment\Aggregates\SavedCard;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Payment\Aggregates\Customer as CoreCustomerAggregate;
use Pagarme\Core\Payment\Repositories\CustomerRepository as CoreCustomerRepository;
use Pagarme\Core\Payment\Repositories\SavedCardRepository as CoreSavedCardRepository;

class CustomerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $moduleCoreSetupMock = Mockery::mock('alias:Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup');
        $moduleCoreSetupMock->shouldReceive('getDatabaseAccessDecorator')->andReturnSelf();

        Brain\Monkey\setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
        Brain\Monkey\tearDown();
    }

    public function testGetWhenPropertyAlreadyExistsShouldPropertyValue()
    {
        $id = 1;
        
        $savedCardRepository = new CoreSavedCardRepository();
        $customerRepository = new CoreCustomerRepository();
        
        $savedCardRepositoryMock = Mockery::mock($savedCardRepository);
        $customerRepositoryMock = Mockery::mock($customerRepository);
        
        $customer = new Customer($id, $savedCardRepositoryMock, $customerRepositoryMock);
        
        $reflectionClass = new ReflectionClass($customer);
        $reflectionProperty = $reflectionClass->getProperty('customer_id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($customer, $id);

        $responseId = $customer->__get('customer_id');

        $this->assertEquals($id, $responseId);
    }

    public function testGetWhenPropertyDoesNotExistsShouldUserMetadataValue()
    {
        $id = 2;
        
        $savedCardRepository = new CoreSavedCardRepository();
        $customerRepository = new CoreCustomerRepository();
        
        $savedCardRepositoryMock = Mockery::mock($savedCardRepository);
        $customerRepositoryMock = Mockery::mock($customerRepository);

        Brain\Monkey\Functions\stubs( [
            'get_user_meta' => $id,
        ] );
        
        $customer = new Customer($id, $savedCardRepositoryMock, $customerRepositoryMock);

        $responseId = $customer->__get('customer_id');

        $this->assertEquals($id, $responseId);
    }

    public function testSetShouldDefinePropertyValue()
    {
        $id = 3;
        
        $savedCardRepository = new CoreSavedCardRepository();
        $customerRepository = new CoreCustomerRepository();

        $savedCardRepositoryMock = Mockery::mock($savedCardRepository);
        $customerRepositoryMock = Mockery::mock($customerRepository);

        Brain\Monkey\Functions\expect('update_user_meta')
            ->once()
            ->with($id, '_pagarme_wc_customer_id', $id);
        
        $customer = new Customer($id, $savedCardRepositoryMock, $customerRepositoryMock);

        $responseCustomer = $customer->__set('customer_id', $id);

        $this->assertEquals($customer, $responseCustomer);
    }

    public function testSetShouldDefineValidCardsPropertyValue()
    {
        $id = 4;
        
        $savedCardRepository = new CoreSavedCardRepository();
        $customerRepository = new CoreCustomerRepository();

        $savedCardRepositoryMock = Mockery::mock($savedCardRepository);
        $customerRepositoryMock = Mockery::mock($customerRepository);

        $savedCard = new SavedCard();
        $expectedCards = [
            $savedCard
        ];
        Brain\Monkey\Functions\expect('update_user_meta')
            ->once()
            ->with($id, '_pagarme_wc_cards', $expectedCards);
        
        $customer = new Customer($id, $savedCardRepositoryMock, $customerRepositoryMock);

        
        $cards = [
            $savedCard,
            null
        ];

        $responseCustomer = $customer->__set('cards', $cards);

        $this->assertEquals($customer, $responseCustomer);
    }

    public function testIssetShouldReturnValue()
    {
        $id = 5;
        
        $savedCardRepository = new CoreSavedCardRepository();
        $customerRepository = new CoreCustomerRepository();
        
        $savedCardRepositoryMock = Mockery::mock($savedCardRepository);
        $customerRepositoryMock = Mockery::mock($customerRepository);
        
        $customer = new Customer($id, $savedCardRepositoryMock, $customerRepositoryMock);
        
        $reflectionClass = new ReflectionClass($customer);
        $reflectionProperty = $reflectionClass->getProperty('customer_id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($customer, $id);

        $responseId = $customer->__isset('customer_id');

        $this->assertEquals($id, $responseId);
    }

    public function testGetPropertyPropertyShouldReturnValue()
    {
        $id = 6;
        
        $savedCardRepository = new CoreSavedCardRepository();
        $customerRepository = new CoreCustomerRepository();
        
        $savedCardRepositoryMock = Mockery::mock($savedCardRepository);
        $customerRepositoryMock = Mockery::mock($customerRepository);

        Brain\Monkey\Functions\stubs( [
            'get_user_meta' => $id,
        ] );
        
        $customer = new Customer($id, $savedCardRepositoryMock, $customerRepositoryMock);

        $responseId = $customer->get_property('customer_id');

        $this->assertEquals($id, $responseId);
    }

    public function testGetPropertyWithCardPropertyShouldReturnValue()
    {
        $id = 7;
        
        $savedCardRepository = new CoreSavedCardRepository();
        $customerRepository = new CoreCustomerRepository();
        
        $savedCards = [new SavedCard()];

        $savedCardRepositoryMock = Mockery::mock($savedCardRepository);
        $savedCardRepositoryMock->shouldReceive('findByOwnerId')
            ->andReturn($savedCards);

        Brain\Monkey\Functions\stubs( [
            'get_user_meta' => null,
        ] );

        $coreCustomerAggregate = new CoreCustomerAggregate();
        $customerIdMock = Mockery::mock(CustomerId::class);
        $coreCustomerAggregateMock = Mockery::mock($coreCustomerAggregate);
        $coreCustomerAggregateMock->shouldReceive('getPagarmeId')
            ->andReturn($customerIdMock);

        $customerRepositoryMock = Mockery::mock($customerRepository);
        $customerRepositoryMock->shouldReceive('findByCode')
            ->andReturn($coreCustomerAggregateMock);
        
        
        $customer = new Customer($id, $savedCardRepositoryMock, $customerRepositoryMock);

        $responseCards = $customer->get_property('cards');

        $this->assertEquals($savedCards, $responseCards);
    }

    public function testGetCardsWithCardsPropertyShouldReturnCardsPropertyValue()
    {
        $id = 8;
        
        $savedCardRepository = new CoreSavedCardRepository();
        $customerRepository = new CoreCustomerRepository();

        $savedCardRepositoryMock = Mockery::mock($savedCardRepository);
        $customerRepositoryMock = Mockery::mock($customerRepository);
        
        $savedCards = [new SavedCard()];
        
        $customer = new Customer($id, $savedCardRepositoryMock, $customerRepositoryMock);

        $reflectionClass = new ReflectionClass($customer);
        $reflectionProperty = $reflectionClass->getProperty('cards');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($customer, $savedCards);

        $responseCards = $customer->get_cards();

        $this->assertSame($savedCards, $responseCards);
    }

    public function testGetCardsWithCustomerNotFoundShouldReturnNull()
    {
        $id = 9;
        
        $savedCardRepository = new CoreSavedCardRepository();
        $customerRepository = new CoreCustomerRepository();

        $savedCardRepositoryMock = Mockery::mock($savedCardRepository);
        $customerRepositoryMock = Mockery::mock($customerRepository);
        $customerRepositoryMock->shouldReceive('findByCode')
            ->andReturnNull();
        
        
        $customer = new Customer($id, $savedCardRepositoryMock, $customerRepositoryMock);

        $responseCards = $customer->get_cards();

        $this->assertNull($responseCards);
    }

    public function testGetCardsWithTypesShouldReturnCards()
    {
        $id = 10;
        
        $savedCardRepository = new CoreSavedCardRepository();
        $customerRepository = new CoreCustomerRepository();

        $savedCards = [new SavedCard()];
        $savedCardRepositoryMock = Mockery::mock($savedCardRepository);
        $savedCardRepositoryMock->shouldReceive('findByOwnerId')
            ->andReturn($savedCards);

        $coreCustomerAggregate = new CoreCustomerAggregate();
        $customerIdMock = Mockery::mock(CustomerId::class);
        $coreCustomerAggregateMock = Mockery::mock($coreCustomerAggregate);
        $coreCustomerAggregateMock->shouldReceive('getPagarmeId')
            ->andReturn($customerIdMock);

        $customerRepositoryMock = Mockery::mock($customerRepository);
        $customerRepositoryMock->shouldReceive('findByCode')
            ->andReturn($coreCustomerAggregateMock);
        
        
        $customer = new Customer($id, $savedCardRepositoryMock, $customerRepositoryMock);

        $types = [
            new stdClass(),
            'type'
        ];
        $responseCards = $customer->get_cards($types);

        $this->assertSame($savedCards, $responseCards);
    }

    public function testGetPagarmeCustomerIdWithCustomerNotFoundShouldReturnFalse()
    {
        $id = 11;
        
        $savedCardRepository = new CoreSavedCardRepository();
        $customerRepository = new CoreCustomerRepository();

        $savedCardRepositoryMock = Mockery::mock($savedCardRepository);
        $customerRepositoryMock = Mockery::mock($customerRepository);
        $customerRepositoryMock->shouldReceive('findByCode')
            ->andReturnNull();
        
        
        $customer = new Customer($id, $savedCardRepositoryMock, $customerRepositoryMock);

        $responsePagarmeCustomerId = $customer->getPagarmeCustomerId();

        $this->assertFalse($responsePagarmeCustomerId);
    }

    public function testGetPagarmeCustomerIdShouldReturnPagarmeCustomerIdValue()
    {
        $id = 12;
        
        $savedCardRepository = new CoreSavedCardRepository();
        $customerRepository = new CoreCustomerRepository();

        $savedCardRepositoryMock = Mockery::mock($savedCardRepository);

        $coreCustomerAggregate = new CoreCustomerAggregate();
        $customerIdMock = Mockery::mock(CustomerId::class);
        $customerIdMock->shouldReceive('getValue')
            ->andReturn($id);
        $coreCustomerAggregateMock = Mockery::mock($coreCustomerAggregate);
        $coreCustomerAggregateMock->shouldReceive('getPagarmeId')
            ->andReturn($customerIdMock);

        $customerRepositoryMock = Mockery::mock($customerRepository);
        $customerRepositoryMock->shouldReceive('findByCode')
            ->andReturn($coreCustomerAggregateMock);
        
        
        $customer = new Customer($id, $savedCardRepositoryMock, $customerRepositoryMock);

        $responsePagarmeCustomerId = $customer->getPagarmeCustomerId();

        $this->assertSame($id, $responsePagarmeCustomerId);
    }
}
