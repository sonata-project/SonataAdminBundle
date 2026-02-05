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

namespace SensioLabs\AdminBundle\Tests\App\ORM\Admin;

use SensioLabs\AdminBundle\Admin\AbstractAdmin;
use SensioLabs\AdminBundle\Datagrid\DatagridMapper;
use SensioLabs\AdminBundle\Datagrid\ListMapper;
use SensioLabs\AdminBundle\Datagrid\ProxyQueryInterface;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\Form\FormMapper;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface as ORMProxyQueryInterface;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Author;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @phpstan-extends AbstractAdmin<Author>
 */
class AuthorAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->addIdentifier('name')
            ->add('number_of_books', FieldDescriptionInterface::TYPE_INTEGER, [
                'accessor' => static fn (Author $author): int => $author->getBooks()->count(),
                'template' => 'author/list_number_of_books_field.html.twig',
            ])
            ->add('numberOfReaders', FieldDescriptionInterface::TYPE_INTEGER, [
                'template' => 'author/list_number_of_readers_field.html.twig',
            ])
            ->addIdentifier('firstBook', null, [
                'sortable' => true,
                'sort_field_mapping' => [
                    'fieldName' => 'name',
                ],
                'sort_parent_association_mappings' => [[
                    'fieldName' => 'books',
                ]],
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('address.street');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('id', TextType::class, [
                'attr' => [
                    'class' => 'author_id',
                ],
                'empty_data' => '',
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'author_name',
                ],
                'empty_data' => '',
            ])
            ->add('address.street', TextType::class, [
                'attr' => [
                    'class' => 'author_address',
                ],
            ]);
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        \assert($query instanceof ORMProxyQueryInterface);
        /** @phpstan-var ORMProxyQueryInterface<Author> $query */
        $alias = $query->getQueryBuilder()->getRootAliases()[0];

        $query
            ->getQueryBuilder()
            ->addSelect('book')
            ->addSelect('reader')
            ->leftJoin(\sprintf('%s.books', $alias), 'book')
            ->leftJoin('book.readers', 'reader');

        return $query;
    }
}
