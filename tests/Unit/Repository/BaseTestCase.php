<?php

namespace App\Tests\Unit\Repository;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BaseTestCase extends KernelTestCase
{
    use FixturesTrait {
        tearDown as protected fixturesTearDown;
    }

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var mixed[]
     */
    protected $initializedReferences;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function getReferences()
    {
        if ($this->initializedReferences === null) {
            foreach ($this->fixtures->getReferences() as $index => $object) {
                $this->initializedReferences[$index] = $this->fixtures->getReference($index);
            }
        }

        return $this->initializedReferences;
    }

    /**
     * @param callable $callback
     * @return array
     */
    public function filterFixtures(callable $callback)
    {
        $values = [];
        foreach ($this->getReferences() as $index => $reference) {
            if ($callback($reference, $index)) {
                $values[$reference->getId()] = $reference;
            }
        }

        return array_values($values);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->fixtures && $this->fixtures->getManager()) {
            $this->fixtures->getManager()->clear();
            $this->fixtures = null;
        }

        $this->fixturesTearDown();

        $this->initializedReferences = null;
        $this->entityManager->close();
        $this->entityManager = null;
    }

}
