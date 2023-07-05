<?php

namespace Pagarme\Core\Test\Abstractions;

use Pagarme\Core\Kernel\Abstractions\AbstractRepository;

abstract class AbstractRepositoryTest extends AbstractSetupTest
{
    /**
     * @var AbstractRepository
     */
    protected $repo;

    public function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->getRepository();
    }

    abstract public function getRepository();
}