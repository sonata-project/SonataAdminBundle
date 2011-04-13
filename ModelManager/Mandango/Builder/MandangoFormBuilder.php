<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\ModelManager\Mandango\Builder;

use Sonata\AdminBundle\Builder\FormBuilderInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormContextInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Form\FieldFactory\FieldFactoryInterface;

/**
 * MandangoFormBuilder.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MandangoFormBuilder implements FormBuilderInterface
{
    private $fieldFactory;
    private $formContext;
    private $validator;

    /**
     * {@inheritdoc}
     */
    public function __construct(FieldFactoryInterface $fieldFactory, FormContextInterface $formContext, ValidatorInterface $validator)
    {
        $this->fieldFactory = $fieldFactory;
        $this->formContext = $formContext;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function addField(Form $form, FieldDescriptionInterface $fieldDescription)
    {
        $field = new \Symfony\Component\Form\TextField($fieldDescription->getFieldName());

        return $form->add($field);
    }

    /**
     * {@inheritdoc}
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription, array $options = array())
    {
        if (!$fieldDescription->getTemplate()) {
             $fieldDescription->setTemplate(sprintf('SonataAdminBundle:CRUD:edit_%s.html.twig', $fieldDescription->getType()));
        }

        $fieldDescription->setAdmin($admin);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseForm($name, $object, array $options = array())
    {
        return new Form($name, array_merge(array(
            'data'      => $object,
            'validator' => $this->validator,
            'context'   => $this->formContext,
        ), $options));
    }
}
