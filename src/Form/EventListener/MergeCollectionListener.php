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

use Doctrine\Common\Collections\Collection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class MergeCollectionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::SUBMIT => ['onBind', 10],
        ];
    }

    public function onBind(FormEvent $event): void
    {
        $collection = $event->getForm()->getData();
        \assert(null === $collection || $collection instanceof Collection);

        $data = $event->getData();
        \assert($data instanceof Collection);

        // looks like there is no way to remove other listeners
        $event->stopPropagation();

        if (null === $collection) {
            $collection = $data;
        } elseif (0 === \count($data)) {
            $collection->clear();
        } else {
            // merge $data into $collection
            foreach ($collection as $model) {
                if (!$data->contains($model)) {
                    $collection->removeElement($model);
                } else {
                    $data->removeElement($model);
                }
            }

            foreach ($data as $model) {
                $collection->add($model);
            }
        }

        $event->setData($collection);
    }
}
