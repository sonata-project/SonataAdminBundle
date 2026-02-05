<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\App\ORM\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Car;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\UlidChildEntity;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\UuidEntity;
use Symfony\Component\Uid\Uuid;

final class UuidEntityFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $car = $this->getReference(CarFixtures::CAR, Car::class);
        $carFrom2010 = $this->getReference(CarFixtures::CAR_FROM_2010, Car::class);
        $carFrom2010 = $this->getReference(CarFixtures::CAR_FROM_2010, Car::class);

        $foo = $this->getReference(UlidChildEntityFixtures::ULID_FOO, UlidChildEntity::class);
        $bar = $this->getReference(UlidChildEntityFixtures::ULID_BAR, UlidChildEntity::class);
        $baz = $this->getReference(UlidChildEntityFixtures::ULID_BAZ, UlidChildEntity::class);
        $qux = $this->getReference(UlidChildEntityFixtures::ULID_QUX, UlidChildEntity::class);

        $uuidEntity = new UuidEntity(Uuid::fromString('018788d3-4bcd-79d7-8acf-b14b787c7e04'));
        $uuidEntity->setName('2000 foo');
        $uuidEntity->setCar($car);
        $uuidEntity->setChild($foo);

        $uuidEntity2 = new UuidEntity(Uuid::fromString('018788d3-4bcd-79d7-8acf-b14b7976583e'));
        $uuidEntity2->setName('2000 bar');
        $uuidEntity2->setCar($car);
        $uuidEntity2->setChild($bar);

        $uuidEntity3 = new UuidEntity(Uuid::fromString('018788d3-4bcd-79d7-8acf-b14b7a3d8247'));
        $uuidEntity3->setName('2010 baz');
        $uuidEntity3->setCar($carFrom2010);
        $uuidEntity3->setChild($baz);

        $uuidEntity4 = new UuidEntity(Uuid::fromString('018788d3-4bcd-79d7-8acf-b14b7afa1674'));
        $uuidEntity4->setName('2010');
        $uuidEntity3->setCar($carFrom2010);

        $uuidEntity5 = new UuidEntity(Uuid::fromString('018788d3-4bcd-79d7-8acf-b14b7b87b53c'));
        $uuidEntity5->setName('qux');
        $uuidEntity5->setChild($qux);

        $uuidEntity6 = new UuidEntity(Uuid::fromString('018788d3-4bcd-79d7-8acf-b14b7b92d176'));
        $uuidEntity6->setName('nothing');

        $manager->persist($uuidEntity);
        $manager->persist($uuidEntity2);
        $manager->persist($uuidEntity3);
        $manager->persist($uuidEntity4);
        $manager->persist($uuidEntity5);
        $manager->persist($uuidEntity6);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CarFixtures::class,
            UlidChildEntityFixtures::class,
        ];
    }
}
