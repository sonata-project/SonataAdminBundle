<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sonata\BaseApplicationBundle\Twig\Extension;


use Bundle\Sonata\BaseApplicationBundle\Admin\FieldDescription;
use Bundle\Sonata\BaseApplicationBundle\Filter\Filter;

class BaseApplicationExtension extends \Twig_Extension
{

    protected $templating;

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            'render_list_element'         => new \Twig_Filter_Method($this, 'renderListElement', array('is_safe' => array('html'))),
            'render_form_element'         => new \Twig_Filter_Method($this, 'renderFormElement', array('is_safe' => array('html'))),
            'render_filter_element'       => new \Twig_Filter_Method($this, 'renderFilterElement', array('is_safe' => array('html'))),
        );
    }

    public function getTokenParsers()
    {

        return array(
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'base_application';
    }

    /**
     * render a list element from the FieldDescription
     *
     * @param  $object
     * @param FieldDescription $fieldDescription
     * @param array $params
     * @return
     */
    public function renderListElement($object, FieldDescription $fieldDescription, $params = array())
    {

        $template = $this->environment->loadTemplate($fieldDescription->getTemplate());

        return $template->render(array_merge($params, array(
            'admin'  => $fieldDescription->getAdmin(),
            'object' => $object,
            'value'  => $this->getValueFromFieldDescription($object, $fieldDescription),
            'field_description' => $fieldDescription
        )));
    }

    /**
     * return the value related to FieldDescription, if the associated object does no
     * exists => a temporary one is created
     *
     * @param  $object
     * @param FieldDescription $fieldDescription
     * @return
     */
    public function getValueFromFieldDescription($object, FieldDescription $fieldDescription)
    {

        $value = $fieldDescription->getValue($object);

        // no value defined, chek if the field_description point to an association
        // if so, create an empty object instance
        // fixme: not sure this is the best place to do that
        if (!$value && $fieldDescription->getAssociationAdmin()) {

            $value = $fieldDescription->getAssociationAdmin()->getNewInstance();
        }

        return $value;
    }

    /**
     * render a filter element
     *
     * @param Filter $filter
     * @param array $params
     * @return
     */
    public function renderFilterElement(Filter $filter, array $params = array())
    {
        $description = $filter->getDescription();

        $template = $this->environment->loadTemplate($description->getTemplate());

        return $template->render(array_merge($params, array(
            'filter' => $filter
        )));
    }

    /**
     * render a field element from the FieldDescription
     *
     *
     * @throws InvalidArgumentException
     * @param FieldDescription $fieldDescription
     * @param  $form
     * @param  $object
     * @param array $params
     * @return string
     */
    public function renderFormElement(FieldDescription $fieldDescription, $form, $object, $params = array())
    {

        if (!$fieldDescription->getFieldName()) {

            return '';
        }

        try {
            $field = $form->get($fieldDescription->getFieldName());
        } catch (\InvalidArgumentException $e) {
            
            throw $e;
        }

        if ($field->isHidden()) {
            return '';
        }

        // find the correct edit parameter
        //  edit   : standard | inline
        //  inline : natural | table
        $parentFieldDescription = $fieldDescription->getAdmin()->getParentFieldDescription();

        if (!$parentFieldDescription) {
            $params['edit']          = $fieldDescription->getOption('edit', 'standard');
            $params['inline']        = $fieldDescription->getOption('inline', 'natural');

            $base_template = sprintf('SonataBaseApplicationBundle:CRUD:base_%s_edit_field.twig.html', 'standard');
        } else {
            $params['edit']          = $parentFieldDescription->getOption('edit', 'standard');
            $params['inline']        = $parentFieldDescription->getOption('inline', 'natural');

            $base_template = sprintf('SonataBaseApplicationBundle:CRUD:base_%s_edit_field.twig.html', $params['edit']);
        }


        $template = $this->environment->loadTemplate($fieldDescription->getTemplate());
        
        return $template->render(array_merge($params, array(
            'admin'             => $fieldDescription->getAdmin(),
            'object'            => $object,
            'field_description' => $fieldDescription,
            'value'             => $this->getValueFromFieldDescription($object, $fieldDescription),
            'field_element'     => $field,
            'base_template'     => $fieldDescription->getOption('base_template', $base_template)
        )));
    }

    /**
     * set the templating engine
     *
     * @param  $templating
     * @return void
     */
    public function setTemplating($templating)
    {
        $this->templating = $templating;
    }

    /**
     * return the templating engine
     *
     * @return Engine
     */
    public function getTemplating()
    {
        return $this->templating;
    }
}

