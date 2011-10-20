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

use Symfony\Component\Form\Extension\Core\Type\TextareaType as FormTextareaType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class RteType extends FormTextareaType
{
    /**
     * @param array $options
     * @return string
     */
    public function getParent(array $options)
    {
        
        return 'textarea';
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @return void
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $view->setAttribute('class', 'sonata-ba-field-rte');
    }
}