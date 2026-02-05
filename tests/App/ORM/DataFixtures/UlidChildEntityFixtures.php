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
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\UlidChildEntity;
use Symfony\Component\Uid\Ulid;

final class UlidChildEntityFixtures extends Fixture implements FixtureInterface
{
    public const ULID_FOO = 'foo';
    public const ULID_BAR = 'bar';
    public const ULID_BAZ = 'baz';
    public const ULID_QUX = 'qux';

    public function load(ObjectManager $manager): void
    {
        $foo = new UlidChildEntity(Ulid::fromString('01GY4D6JYD2KCVDCJS0JF17X90'), 'foo');
        $bar = new UlidChildEntity(Ulid::fromString('01GY4D6JYD2KCVDCJS0JF17X91'), 'bar');
        $baz = new UlidChildEntity(Ulid::fromString('01GY4D6JYD2KCVDCJS0JF17X92'), 'baz');
        $qux = new UlidChildEntity(Ulid::fromString('01GY4D6JYD2KCVDCJS0JF17X93'), 'qux');
        $more = new UlidChildEntity(Ulid::fromString('01GYC5KGQ737PJVKRKD4NXW6VP'), 'more');

        $manager->persist($foo);
        $manager->persist($bar);
        $manager->persist($baz);
        $manager->persist($qux);
        $manager->persist($more);

        $manager->flush();

        $this->addReference(self::ULID_FOO, $foo);
        $this->addReference(self::ULID_BAR, $bar);
        $this->addReference(self::ULID_BAZ, $baz);
        $this->addReference(self::ULID_QUX, $qux);
    }
}
