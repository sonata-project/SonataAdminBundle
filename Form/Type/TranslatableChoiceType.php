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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilder;

class TranslatableChoiceType extends ChoiceType
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
     * @param array $options
     * @return array
     */
    public function getDefaultOptions(array $options)
    {
        $multiple = isset($options['multiple']) && $options['multiple'];
        $expanded = isset($options['expanded']) && $options['expanded'];

        return array(
            'multiple'          => false,
            'expanded'          => false,
            'choice_list'       => null,
            'choices'           => array(),
            'preferred_choices' => array(),
            'catalogue'         => 'messages',
            'empty_data'        => $multiple || $expanded ? array() : '',
            'empty_value'       => $multiple || $expanded || !isset($options['empty_value']) ? null : '',
            'error_bubbling'    => false,
        );
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->setAttribute('catalogue', $options['catalogue']);
    }

    public function buildView(FormView $view, FormInterface $form)
    {
        parent::buildView($view, $form);

        $choices = array();
        $catalogue = $form->getAttribute('catalogue');

        foreach ($view->get('choices') as $name => $value) {
            $choices[$name] = $this->translator->trans($value, array(), $catalogue);
        }

        $view->set('choices', $choices);
    }
}