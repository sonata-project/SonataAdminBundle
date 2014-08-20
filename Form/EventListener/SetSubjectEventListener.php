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

namespace Sonata\AdminBundle\Form\EventListener;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Set the admin's subject in the event.
 *
 * @author Uwe Jäger <uwej711@googlemail.com>
 */
class SetSubjectEventListener implements EventSubscriberInterface
{
    /** @var AdminInterface */
    protected $admin;

    /**
     * @param AdminInterface $admin
     */
    public function __construct(AdminInterface $admin)
    {
        $this->admin = $admin;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA    => 'preSetData',
        );
    }

    public function preSetData(FormEvent $event)
    {
        if (null !== $event->getData()) {
            $this->admin->setSubject($event->getData());
            $this->admin->reConfigureFormFields($event->getForm());
        }
    }
}