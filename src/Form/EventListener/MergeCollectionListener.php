<?php

declare(strict_types=1);

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
final class MergeCollectionListener implements EventSubscriberInterface
{
    /**
     * @var ModelManagerInterface
     */
    private $modelManager;

    public function __construct(ModelManagerInterface $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => ['onBind', 10],
        ];
    }

    public function onBind(FormEvent $event): void
    {
        $collection = $event->getForm()->getData();
        $data = $event->getData();

        // looks like there is no way to remove other listeners
        $event->stopPropagation();

        if (!$collection) {
            $collection = $data;
        } elseif (0 === \count($data)) {
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
