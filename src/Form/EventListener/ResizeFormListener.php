<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Resize a collection form element based on the data sent from the client.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
final class ResizeFormListener implements EventSubscriberInterface
{
    /**
     * @param array<string, mixed> $typeOptions
     */
    public function __construct(
        private string $type,
        private array $typeOptions = [],
        private bool $resizeOnSubmit = false,
        private ?\Closure $preSubmitDataCallback = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
            FormEvents::SUBMIT => 'onSubmit',
        ];
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function preSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = [];
        }

        if (!\is_array($data) && !$data instanceof \Traversable) {
            throw new UnexpectedTypeException($data, 'array or \Traversable');
        }

        // First remove all rows except for the prototype row
        // Type cast to string, because Symfony form can return integer keys
        foreach ($form as $name => $child) {
            $form->remove((string) $name);
        }

        // Then add all rows again in the correct order
        foreach ($data as $name => $value) {
            $options = array_merge($this->typeOptions, [
                'property_path' => '['.$name.']',
                'data' => $value,
            ]);

            $name = \is_int($name) ? (string) $name : $name;

            $form->add($name, $this->type, $options);
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function preSubmit(FormEvent $event): void
    {
        if (!$this->resizeOnSubmit) {
            return;
        }

        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data || '' === $data) {
            $data = [];
        }

        if (
            !\is_array($data)
            && (!$data instanceof \Traversable || !$data instanceof \ArrayAccess)
        ) {
            throw new UnexpectedTypeException($data, 'array or \Traversable&\ArrayAccess');
        }

        // Remove all empty rows except for the prototype row
        // Type cast to string, because Symfony form can return integer keys
        foreach ($form as $name => $child) {
            $form->remove((string) $name);
        }

        // Add all additional rows
        foreach ($data as $name => $value) {
            // remove selected elements before adding them again
            if (isset($value['_delete'])) {
                unset($data[$name]);

                continue;
            }

            // Type cast to string, because Symfony form can returns integer keys
            if (!$form->has((string) $name)) {
                $buildOptions = [
                    'property_path' => '['.$name.']',
                ];

                if (null !== $this->preSubmitDataCallback) {
                    $buildOptions['data'] = \call_user_func($this->preSubmitDataCallback, $value);
                }

                $options = array_merge($this->typeOptions, $buildOptions);

                $name = \is_int($name) ? (string) $name : $name;

                $form->add($name, $this->type, $options);
            }
        }

        $event->setData($data);
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function onSubmit(FormEvent $event): void
    {
        if (!$this->resizeOnSubmit) {
            return;
        }

        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = [];
        }

        if (
            !\is_array($data)
            && (!$data instanceof \Traversable || !$data instanceof \ArrayAccess)
        ) {
            throw new UnexpectedTypeException($data, 'array or \Traversable&\ArrayAccess');
        }

        foreach ($data as $name => $child) {
            // Type cast to string, because Symfony form can returns integer keys
            if (!$form->has((string) $name)) {
                unset($data[$name]);
            }
        }

        $event->setData($data);
    }
}
