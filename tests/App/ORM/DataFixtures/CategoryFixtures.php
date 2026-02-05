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
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Category;

final class CategoryFixtures extends Fixture
{
    public const CATEGORY = 'category_novel';

    public function load(ObjectManager $manager): void
    {
        $dystopianCategory = new Category('category_dystopian', 'Dystopian');
        $novelCategory = new Category('category_novel', 'Novel');

        $manager->persist($novelCategory);
        $manager->persist($dystopianCategory);
        $manager->flush();

        $this->addReference(self::CATEGORY, $novelCategory);
    }
}
