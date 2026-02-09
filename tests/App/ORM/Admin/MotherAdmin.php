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
use SensioLabs\AdminBundle\Datagrid\ListMapper;
use SensioLabs\AdminBundle\Form\FormMapper;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Mother;
use SensioLabs\AdminBundle\Form\Type\SensioLabsCollectionType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @phpstan-extends AbstractAdmin<Mother>
 */
final class MotherAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('children', SensioLabsCollectionType::class, [
            'by_reference' => false,
            'constraints' => [
                new Assert\Valid(),
            ],
        ], [
            'edit' => 'inline',
            'inline' => 'table',
        ]);
    }
}
