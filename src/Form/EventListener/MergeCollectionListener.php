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
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class MergeCollectionListener implements EventSubscriberInterface
{
    /**
     * @var ModelManagerInterface|null
     */
    protected $modelManager;

    /**
     * NEXT_MAJOR: Remove this constructor and the modelManager property.
     */
    public function __construct(?ModelManagerInterface $modelManager = null)
    {
        if (null !== $modelManager) {
            @trigger_error(sprintf(
                'Passing argument 1 to %s() is deprecated since sonata-project/admin-bundle 3.75'
                .' and will be ignored in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        $this->modelManager = $modelManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => ['onBind', 10],
        ];
    }

    public function onBind(FormEvent $event)
    {
        $collection = $event->getForm()->getData();
        \assert(null === $collection || $collection instanceof Collection);

        $data = $event->getData();
        \assert($data instanceof Collection);

        // looks like there is no way to remove other listeners
        $event->stopPropagation();

        if (!$collection) {
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
