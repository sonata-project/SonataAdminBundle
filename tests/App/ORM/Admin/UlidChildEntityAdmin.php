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
use SensioLabs\AdminBundle\Form\FormMapper;
use SensioLabs\AdminBundle\Form\Type\ModelAutocompleteType;
use SensioLabs\AdminBundle\Show\ShowMapper;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\UlidChildEntity;
use Symfony\Component\Uid\Ulid;

/**
 * @phpstan-extends AbstractAdmin<UlidChildEntity>
 */
final class UlidChildEntityAdmin extends AbstractAdmin
{
    protected function createNewInstance(): UlidChildEntity
    {
        return new UlidChildEntity(new Ulid());
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('name')
            ->add('parent', null, [
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'name',
                    'multiple' => true,
                ],
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('name')
            ->add('parent');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('name')
            ->add('parent');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name')
            ->add('parent');
    }
}
