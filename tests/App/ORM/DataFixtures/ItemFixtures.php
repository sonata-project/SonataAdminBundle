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
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Command;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Item;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Product;

final class ItemFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $command1 = $this->getReference(CommandFixtures::COMMAND_1, Command::class);
        \assert($command1 instanceof Command);
        $command2 = $this->getReference(CommandFixtures::COMMAND_2, Command::class);
        \assert($command2 instanceof Command);
        $product1 = $this->getReference(ProductFixtures::PRODUCT_1, Product::class);
        \assert($product1 instanceof Product);
        $product2 = $this->getReference(ProductFixtures::PRODUCT_2, Product::class);
        \assert($product2 instanceof Product);

        $item1 = new Item($command1, $product1);
        $item2 = new Item($command1, $product2);
        $item3 = new Item($command2, $product2);

        $manager->persist($item1);
        $manager->persist($item2);
        $manager->persist($item3);
        $manager->flush();
    }

    /**
     * @phpstan-return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
            CommandFixtures::class,
        ];
    }
}
