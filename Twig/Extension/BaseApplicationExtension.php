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

use Symfony\Bundle\TwigBundle\TokenParser\HelperTokenParser;

class BaseApplicationExtension extends \Twig_Extension
{

    protected $templating;

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

    public function renderListElement($object, $field_description, $params = array())
    {
        $value = null;

        if(isset($field_description['reflection'])) {

            $value = $field_description['reflection']->getValue($object);

        } else if(method_exists($object, $field_description['code'])) {

            $value = call_user_func(array($object, $field_description['code']));
            
        }

        return $this->templating->render($field_description['template'], array_merge($params, array(
            'object' => $object,
            'value'  => $value,
            'field_description' => $field_description
        )));
    }

    public function renderFilterElement($filter, $params = array())
    {
        $description = $filter->getDescription();

        return $this->templating->render($description['template'], array_merge($params, array(
            'filter' => $filter
        )));
    }

    public function renderFormElement($field_description, $form, $object, $params = array())
    {

        if(!isset($field_description['fieldName'])) {
            return '';
        }
        
        $field = $form->get($field_description['fieldName']);

        if($field->isHidden()) {
            return '';
        }

        return $this->templating->render($field_description['template'], array_merge($params, array(
            'object'            => $object,
            'field_description' => $field_description,
            'field_element'     => $form->get($field_description['fieldName']),
        )));
    }

    public function setTemplating($templating)
    {
        $this->templating = $templating;
    }

    public function getTemplating()
    {
        return $this->templating;
    }
}

