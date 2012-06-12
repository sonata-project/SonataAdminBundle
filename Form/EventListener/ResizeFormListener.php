<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\EventListener;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\Form\Event\FilterDataEvent;
use Symfony\Component\Form\FormFactoryInterface;
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
     * @var string
     */
    private $type;

    /**
     * @var Boolean
     */
    private $resizeOnBind;

    private $typeOptions;

    private $removed = array();

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $factory
     * @param string                                       $type
     * @param array                                        $typeOptions
     * @param bool                                         $resizeOnBind
     */
    public function __construct(FormFactoryInterface $factory, $type, array $typeOptions = array(), $resizeOnBind = false)
    {
        $this->factory      = $factory;
        $this->type         = $type;
        $this->resizeOnBind = $resizeOnBind;
        $this->typeOptions  = $typeOptions;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA    => 'preSetData',
            FormEvents::PRE_BIND        => 'preBind',
            FormEvents::BIND            => 'onBind',
        );
    }

    /**
     * @param \Symfony\Component\Form\Event\DataEvent $event
     *
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     */
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

        // First remove all rows except for the prototype row
        foreach ($form as $name => $child) {
            $form->remove($name);
        }

        // Then add all rows again in the correct order
        foreach ($data as $name => $value) {
            $options = array_merge($this->typeOptions, array(
                'property_path' => '[' . $name . ']',
            ));

            $form->add($this->factory->createNamed($name, $this->type, $value, $options));
        }
    }

    /**
     * @param \Symfony\Component\Form\Event\DataEvent $event
     *
     * @return mixed
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     */
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

        // Remove all empty rows except for the prototype row
        foreach ($form as $name => $child) {
            $form->remove($name);
        }

        // Add all additional rows
        foreach ($data as $name => $value) {
            if (!$form->has($name)) {
                $options = array_merge($this->typeOptions, array(
                    'property_path' => '[' . $name . ']',
                ));

                $form->add(new FormProxy($this->factory->createNamed($name, $this->type, null, $options), isset($value['_delete'])));
            }

            if (isset($value['_delete'])) {
                $this->removed[] = $name;
            }
        }
    }

    /**
     * @param \Symfony\Component\Form\Event\FilterDataEvent $event
     *
     * @return mixed
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function onBind(FilterDataEvent $event)
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

        // remove selected elements
        foreach ($this->removed as $pos) {
            unset($data[$pos]);
        }

        $event->setData($data);
    }
}
