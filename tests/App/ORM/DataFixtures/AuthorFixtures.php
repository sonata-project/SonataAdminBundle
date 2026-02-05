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
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Address;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Author;

final class AuthorFixtures extends Fixture
{
    public const AUTHOR = 'author';
    public const AUTHOR_WITH_TWO_BOOKS = 'author_with_two_books';

    public function load(ObjectManager $manager): void
    {
        $author = new Author('miguel_de_cervantes', 'Miguel de Cervantes');
        $author->setAddress(new Address('Somewhere in La Mancha, in a place whose name I do not care to remember'));

        $manager->persist($author);
        $manager->persist(new Author('anonymous', 'Anonymous'));

        $authorWithTwoBooks = new Author('author_with_two_books', 'Author with 2 books');
        $manager->persist($authorWithTwoBooks);

        $authorForAutocompletion = new Author('autocompletion_author', 'autocompletion author');
        $manager->persist($authorForAutocompletion);

        $manager->flush();

        $this->addReference(self::AUTHOR, $author);
        $this->addReference(self::AUTHOR_WITH_TWO_BOOKS, $authorWithTwoBooks);
    }
}
