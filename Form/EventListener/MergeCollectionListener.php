<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\EventListener;

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class MergeCollectionListener implements EventSubscriberInterface
{
    /**
     * @var ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @param ModelManagerInterface $modelManager
     */
    public function __construct(ModelManagerInterface $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::SUBMIT => array('onBind', 10),
        );
    }

    /**
     * @param FormEvent $event
     */
    public function onBind(FormEvent $event)
    {
        $collection = $event->getForm()->getData();
        $data = $event->getData();

        // looks like there is no way to remove other listeners
        $event->stopPropagation();

        if (!$collection) {
            $collection = $data;
        } elseif (count($data) === 0) {
            $this->modelManager->collectionClear($collection);
        } else {
            // merge $data into $collection
            foreach ($collection as $entity) {
                if (!$this->modelManager->collectionHasElement($data, $entity)) {
                    $this->modelManager->collectionRemoveElement($collection, $entity);
                } else {
                    $this->modelManager->collectionRemoveElement($data, $entity);
                }
            }

            foreach ($data as $entity) {
                $this->modelManager->collectionAddElement($collection, $entity);
            }
        }

        $event->setData($collection);
    }
}
