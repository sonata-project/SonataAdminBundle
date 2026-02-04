<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\App\Admin;

use SensioLabs\AdminBundle\Admin\AbstractAdmin;
use SensioLabs\AdminBundle\Datagrid\ListMapper;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\Form\FormMapper;
use SensioLabs\AdminBundle\Show\ShowMapper;
use SensioLabs\AdminBundle\Tests\App\Model\Translated;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @phpstan-extends AbstractAdmin<Translated>
 */
final class TranslatedAdmin extends AbstractAdmin
{
    protected function createNewInstance(): object
    {
        return new Translated();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->add('name_list', FieldDescriptionInterface::TYPE_STRING);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name_form', TextType::class, ['help' => 'Help me!'])
            ->ifTrue($this->getSubject()->isPublished)
                ->add('datePublished')
            ->ifEnd();
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('name_show', FieldDescriptionInterface::TYPE_STRING);
    }
}
