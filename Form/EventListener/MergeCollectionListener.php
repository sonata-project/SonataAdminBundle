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

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Sonata\AdminBundle\Model\ModelManagerInterface;

class MergeCollectionListener implements EventSubscriberInterface
{
    protected $modelManager;

    /**
     * @param \Sonata\AdminBundle\Model\ModelManagerInterface $modelManager
     */
    public function __construct(ModelManagerInterface $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::SUBMIT => array('onBind', 10),
        );
    }

    /**
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function onBind(FormEvent $event)
    {
        $collection = $event->getForm()->getData();
        $data       = $event->getData();

        // looks like there is no way to remove other listeners
        $event->stopPropagation();

        if (!$collection) {
            $collection = $data;
        } elseif (count($data) === 0) {
            $this->modelManager->collectionClear($collection);
        } else {
            // merge $data into $collection
            $newCollection = new \Doctrine\Common\Collections\ArrayCollection();
            foreach ($data as $entity) {
                if ($this->modelManager->collectionHasElement($collection, $entity)) {
                    $this->modelManager->collectionAddElement($newCollection, $entity);
                }
            }
            $collection = $newCollection;
        }

        $event->setData($collection);
    }
}
