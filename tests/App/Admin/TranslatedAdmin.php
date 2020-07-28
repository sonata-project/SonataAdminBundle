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

namespace Sonata\AdminBundle\Tests\App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class TranslatedAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list)
    {
        $list->add('name_list', TemplateRegistry::TYPE_STRING);
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('name_form', TextType::class, ['help' => 'Help me!'])
            ->ifTrue($this->getSubject()->isPublished)
                ->add('datePublished')
            ->ifEnd();
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show->add('name_show', TemplateRegistry::TYPE_STRING);
    }
}
