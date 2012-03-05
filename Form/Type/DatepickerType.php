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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DatepickerType extends AbstractType
{

    public function buildForm(FormBuilder $builder, array $options)
    {

        parent::buildForm($builder, $options);

        $formatter = new \IntlDateFormatter(
            \Locale::getDefault(),
            $options['format'],
            \IntlDateFormatter::NONE,
            null,
            \IntlDateFormatter::GREGORIAN,
            $options['pattern']
        );

        $builder->setAttribute('formatter', $formatter)
                ->setAttribute('locale', $options['locale'])
        ;

    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'format'         => \IntlDateFormatter::SHORT,
            'locale' => 'en-GB',
            'widget' => 'single_text',
        );
    }

    public function getParent(array $options)
    {
        return 'date';
    }

    public function getName()
    {
        return 'datepicker';
    }

    public function buildView(FormView $view, FormInterface $form)
    {
        $view   ->setAttribute('class', 'sonata-datepicker')
                ->set('locale', $form->getAttribute('locale'))
                ->set('value', $form->getAttribute('formatter')->format(strtotime($view->get('value'))))
                ->set('pattern', '')
        ;
    }
}
