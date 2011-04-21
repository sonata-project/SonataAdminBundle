<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\EventListener;

use Symfony\Component\Form\Events;
use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\Form\Event\FilterDataEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Resize a collection form element based on the data sent from the client.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class ResizeFormListener implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;

    /**
     * @var array
     */
    private $prototype;

    /**
     * @var bool
     */
    private $resizeOnBind;

    public function __construct(FormFactoryInterface $factory, $prototype, $resizeOnBind = false)
    {
        $this->factory = $factory;
        $this->prototype = $prototype;
        $this->resizeOnBind = $resizeOnBind;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::preSetData,
            Events::preBind,
            Events::onBindNormData,
        );
    }

    public function preSetData(DataEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = array();
        }

        if (!is_array($data) && !$data instanceof \Traversable) {
            throw new UnexpectedTypeException($data, 'array or \Traversable');
        }

        // at this point the data should a Collection with
        //  $name = the position
        //  $value = the object
        foreach ($data as $name => $value) {
            $form->add($this->factory->create('text', $name, array(
                'property_path' => '['.$name.']',
            )));

            $subChildForm = $form->get($name);

            $prototype = clone $this->prototype;
            $prototype->setData($value);
            $prototypeForm = $prototype->getForm();

            foreach($prototypeForm->getChildren() as $field) {
                $subChildForm->add($field);
            }

            $form->add($subChildForm);
        }

        // todo : deal with min and max value
    }

    public function preBind(DataEvent $event)
    {
        if (!$this->resizeOnBind) {
            return;
        }

        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data || '' === $data) {
            $data = array();
        }

        if (!is_array($data) && !$data instanceof \Traversable) {
            throw new UnexpectedTypeException($data, 'array or \Traversable');
        }

        // Add all additional rows
        foreach ($data as $name => $value) {
            if (!$form->has($name)) {
                $form->add($this->factory->create($this->type, $name, array(
                    'property_path' => '['.$name.']',
                )));

                $subChildForm = $form->get($name);

                $prototype = clone $this->prototype;
                $prototype->setData($value);
                $prototypeForm = $prototype->getForm();

                foreach($prototypeForm->getChildren() as $field) {
                    $subChildForm->add($field);
                }

                $form->add($subChildForm);
            }
        }
    }

    public function onBindNormData(FilterDataEvent $event)
    {
        if (!$this->resizeOnBind) {
            return;
        }

        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = array();
        }

        if (!is_array($data) && !$data instanceof \Traversable) {
            throw new UnexpectedTypeException($data, 'array or \Traversable');
        }

        foreach ($data as $name => $child) {
            if (!$form->has($name)) {
                unset($data[$name]);
            }
        }

        $event->setData($data);
    }
}