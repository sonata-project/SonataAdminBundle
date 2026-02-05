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
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Car;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @phpstan-extends AbstractAdmin<Car>
 */
final class CarAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('name')
            ->add('year');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('year');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', TextType::class)
            ->add('year', IntegerType::class);
    }
}
