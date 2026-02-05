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
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Product;

final class ProductFixtures extends Fixture
{
    public const PRODUCT_1 = 'product_1';
    public const PRODUCT_2 = 'product_2';

    public function load(ObjectManager $manager): void
    {
        $product1 = new Product(1, 'Knife', '3.0');
        $product2 = new Product(2, 'Fork', '2.0');

        $manager->persist($product1);
        $manager->persist($product2);
        $manager->flush();

        $this->addReference(self::PRODUCT_1, $product1);
        $this->addReference(self::PRODUCT_2, $product2);
    }
}
