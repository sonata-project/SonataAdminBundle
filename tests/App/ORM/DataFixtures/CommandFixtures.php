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
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Command;

final class CommandFixtures extends Fixture
{
    public const COMMAND_1 = 'command_1';
    public const COMMAND_2 = 'command_2';

    public function load(ObjectManager $manager): void
    {
        $command1 = new Command(1);
        $command2 = new Command(2);

        $manager->persist($command1);
        $manager->persist($command2);
        $manager->flush();

        $this->addReference(self::COMMAND_1, $command1);
        $this->addReference(self::COMMAND_2, $command2);
    }
}
