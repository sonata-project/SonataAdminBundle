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

use SensioLabs\AdminBundle\Datagrid\ORM\Pager;
use SensioLabs\AdminBundle\Tests\App\ORM\Admin\AuthorAdmin;
use SensioLabs\AdminBundle\Tests\App\ORM\Admin\AuthorWithSimplePagerAdmin;
use SensioLabs\AdminBundle\Tests\App\ORM\Admin\BookAdmin;
use SensioLabs\AdminBundle\Tests\App\ORM\Admin\BookWithAuthorAutocompleteAdmin;
use SensioLabs\AdminBundle\Tests\App\ORM\Admin\CarAdmin;
use SensioLabs\AdminBundle\Tests\App\ORM\Admin\CategoryAdmin;
use SensioLabs\AdminBundle\Tests\App\ORM\Admin\ChildAdmin;
use SensioLabs\AdminBundle\Tests\App\ORM\Admin\ItemAdmin;
use SensioLabs\AdminBundle\Tests\App\ORM\Admin\MotherAdmin;
use SensioLabs\AdminBundle\Tests\App\ORM\Admin\SubAdmin;
use SensioLabs\AdminBundle\Tests\App\ORM\Admin\UlidChildEntityAdmin;
use SensioLabs\AdminBundle\Tests\App\ORM\Admin\UuidEntityAdmin;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Author;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Book;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Car;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Category;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Child;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Item;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Mother;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Sub;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\UlidChildEntity;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\UuidEntity;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->load('SensioLabs\\AdminBundle\\Tests\\App\\ORM\\DataFixtures\\', dirname(__DIR__).'/DataFixtures')

        ->set(CategoryAdmin::class)
            ->tag('sensiolabs.admin', [
                'manager_type' => 'orm',
                'model_class' => Category::class,
                'label' => 'Category',
            ])

        ->set(BookAdmin::class)
            ->tag('sensiolabs.admin', [
                'manager_type' => 'orm',
                'model_class' => Book::class,
                'label' => 'Book',
                'default' => true,
            ])

        ->set(BookWithAuthorAutocompleteAdmin::class)
            ->tag('sensiolabs.admin', [
                'manager_type' => 'orm',
                'model_class' => Book::class,
                'label' => 'Book with Author autocomplete',
            ])

        ->set(AuthorAdmin::class)
            ->tag('sensiolabs.admin', [
                'manager_type' => 'orm',
                'model_class' => Author::class,
                'label' => 'Author',
                'default' => true,
            ])
            ->call('setTemplate', ['outer_list_rows_list', 'author/list_outer_list_rows_list.html.twig'])

        ->set(AuthorWithSimplePagerAdmin::class)
            ->tag('sensiolabs.admin', [
                'manager_type' => 'orm',
                'model_class' => Author::class,
                'label' => 'Author with Simple Pager',
                'pager_type' => Pager::TYPE_SIMPLE,
            ])
            ->call('setTemplate', ['outer_list_rows_list', 'author/list_outer_list_rows_list.html.twig'])

        ->set(CarAdmin::class)
            ->tag('sensiolabs.admin', [
                'manager_type' => 'orm',
                'model_class' => Car::class,
                'label' => 'Car',
            ])

        ->set(ItemAdmin::class)
            ->tag('sensiolabs.admin', [
                'manager_type' => 'orm',
                'model_class' => Item::class,
                'label' => 'Command item',
            ])

        ->set(SubAdmin::class)
            ->tag('sensiolabs.admin', [
                'manager_type' => 'orm',
                'model_class' => Sub::class,
                'label' => 'Inheritance',
            ])

        ->set(MotherAdmin::class)
            ->tag('sensiolabs.admin', [
                'manager_type' => 'orm',
                'model_class' => Mother::class,
                'label' => 'Mother',
            ])

        ->set(ChildAdmin::class)
            ->tag('sensiolabs.admin', [
                'manager_type' => 'orm',
                'model_class' => Child::class,
                'label' => 'Child',
            ])

        ->set(UuidEntityAdmin::class)
            ->tag('sensiolabs.admin', [
                'manager_type' => 'orm',
                'model_class' => UuidEntity::class,
                'label' => 'UuidEntity',
            ])

        ->set(UlidChildEntityAdmin::class)
            ->tag('sensiolabs.admin', [
                'manager_type' => 'orm',
                'model_class' => UlidChildEntity::class,
                'label' => 'UlidChildEntity',
            ]);
};
