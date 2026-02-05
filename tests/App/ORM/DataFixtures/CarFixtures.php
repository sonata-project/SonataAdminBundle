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
use Doctrine\Persistence\ObjectManager;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Car;

final class CarFixtures extends Fixture
{
    public const CAR = 'car';
    public const CAR_FROM_2010 = 'car_from_2010';

    public function load(ObjectManager $manager): void
    {
        $foo2000 = new Car('Foo', 2000);
        $foo2010 = new Car('Foo', 2010);
        $bar2000 = new Car('Bar', 2000);

        $manager->persist($foo2000);
        $manager->persist($foo2010);
        $manager->persist($bar2000);
        $manager->flush();

        $this->addReference(self::CAR, $foo2000);
        $this->addReference(self::CAR_FROM_2010, $foo2010);
    }
}
