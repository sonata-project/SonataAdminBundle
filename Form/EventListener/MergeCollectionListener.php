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

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\FilterDataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Sonata\AdminBundle\Model\ModelManagerInterface;

class MergeCollectionListener implements EventSubscriberInterface
{
    protected $modelManager;

    public function __construct(ModelManagerInterface $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    public static function getSubscribedEvents()
    {
        return array(FormEvents::BIND_NORM_DATA => 'onBindNormData');
    }

    public function onBindNormData(FilterDataEvent $event)
    {
        $collection = $event->getForm()->getData();
        $data       = $event->getData();

        if (!$collection) {
            $collection = $data;
        } else if (count($data) === 0) {
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
