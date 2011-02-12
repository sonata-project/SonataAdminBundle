<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BaseApplicationBundle\Builder;

use Sonata\BaseApplicationBundle\Admin\FieldDescription;
use Sonata\BaseApplicationBundle\Admin\Admin;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormContextInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Form\FieldFactory\FieldFactoryInterface;

interface FormBuilderInterface
{

    function __construct(FieldFactoryInterface $fieldFactory, FormContextInterface $formContext, ValidatorInterface $validator);

    function addField(Form $form, FieldDescription $fieldDescription);

    function fixFieldDescription(Admin $admin, FieldDescription $fieldDescription, array $options = array());

    function getBaseForm($name, $object, array $options = array());
}
