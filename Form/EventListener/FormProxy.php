<?php

namespace Sonata\AdminBundle\Form\EventListener;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormView;

class FormProxy implements \IteratorAggregate, FormInterface
{
    protected $form;

    protected $bypassValidation;

    public function __construct($form, $bypassValidation)
    {
        $this->form = $form;
        $this->bypassValidation = $bypassValidation;
    }

    public function isValid()
    {
        if ($this->bypassValidation) {
            return true;
        }

        return $this->__call('isValid', func_get_args());
    }

    public function getErrors()
    {
        if ($this->bypassValidation) {
            return array();
        }

        return $this->__call('getErrors', func_get_args());
    }

    public function getIterator()
    {
        return $this->form;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->form, $name), $arguments);
    }

    //Only proxied functions after this limit

    public function setParent(FormInterface $parent = null)
    {
        return $this->__call('setParent', func_get_args());
    }

    public function getParent()
    {
        return $this->__call('getParent', func_get_args());
    }

    public function hasParent()
    {
        return $this->__call('hasParent', func_get_args());
    }

    public function add(FormInterface $child)
    {
        return $this->__call('add', func_get_args());
    }

    public function get($name)
    {
        return $this->__call('get', func_get_args());
    }

    public function has($name)
    {
        return $this->__call('has', func_get_args());
    }

    public function remove($name)
    {
        return $this->__call('remove', func_get_args());
    }

    public function all()
    {
        return $this->__call('all', func_get_args());
    }

    public function setData($modelData)
    {
        return $this->__call('setData', func_get_args());
    }

    public function getData()
    {
        return $this->__call('getData', func_get_args());
    }

    public function getNormData()
    {
        return $this->__call('getNormData', func_get_args());
    }

    public function getViewData()
    {
        return $this->__call('getViewData', func_get_args());
    }

    public function getExtraData()
    {
        return $this->__call('getExtraData', func_get_args());
    }

    public function getConfig()
    {
        return $this->__call('getConfig', func_get_args());
    }

    public function isBound()
    {
        return $this->__call('isBound', func_get_args());
    }

    public function getName()
    {
        return $this->__call('getName', func_get_args());
    }

    public function getPropertyPath()
    {
        return $this->__call('getPropertyPath', func_get_args());
    }

    public function addError(FormError $error)
    {
        return $this->__call('addError', func_get_args());
    }

    public function isRequired()
    {
        return $this->__call('isRequired', func_get_args());
    }

    public function isDisabled()
    {
        return $this->__call('isDisabled', func_get_args());
    }

    public function isEmpty()
    {
        return $this->__call('isEmpty', func_get_args());
    }

    public function isSynchronized()
    {
        return $this->__call('isSynchronized', func_get_args());
    }

    public function bind($data)
    {
        return $this->__call('bind', func_get_args());
    }

    public function getRoot()
    {
        return $this->__call('getRoot', func_get_args());
    }

    public function isRoot()
    {
        return $this->__call('isRoot', func_get_args());
    }

    public function createView(FormView $parent = null)
    {
        return $this->__call('createView', func_get_args());
    }

    public function offsetExists($offset)
    {
        return $this->__call('offsetExists', func_get_args());
    }

    public function offsetGet($offset)
    {
        return $this->__call('offsetGet', func_get_args());
    }

    public function offsetSet($offset, $value)
    {
        return $this->__call('offsetSet', func_get_args());
    }

    public function offsetUnset($offset)
    {
        return $this->__call('offsetUnset', func_get_args());
    }

    public function count()
    {
        return $this->__call('count', func_get_args());
    }
}
