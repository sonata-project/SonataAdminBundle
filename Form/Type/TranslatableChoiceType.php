<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\AdminBundle\Form\Type;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TranslatableChoiceType extends AbstractType
{
    protected $translator;

    /**
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'catalogue' => 'messages',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // translate options before building form
        foreach ($view->vars['choices'] as $choiceView) {
            $choiceView->label = $this->translator->trans($choiceView->label, array(), $options['catalogue']);
        }

        // translate preferred options
        foreach ($view->vars['preferred_choices'] as $choiceView) {
            $choiceView->label = $this->translator->trans($choiceView->label, array(), $options['catalogue']);
        }

        // translate empty value
        if (!empty($view->vars['empty_value'])) {
            $view->vars['empty_value'] = $this->translator->trans($view->vars['empty_value'], array(), $options['catalogue']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_translatable_choice';
    }
}
