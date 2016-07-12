<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
abstract class AbstractListBuilder implements ListBuilderInterface
{
    /**
     * Generic method for action buttons.
     *
     * NEXT_MAJOR: Make this method final and add it to ListBuilderInterface.
     *
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return FieldDescriptionInterface
     */
    public function buildActionFieldDescription(FieldDescriptionInterface $fieldDescription)
    {
        if (null === $fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list__action.html.twig');
        }

        if (null === $fieldDescription->getType()) {
            $fieldDescription->setType('action');
        }

        if (null === $fieldDescription->getOption('name')) {
            $fieldDescription->setOption('name', 'Action');
        }

        if (null === $fieldDescription->getOption('code')) {
            $fieldDescription->setOption('code', 'Action');
        }

        if (null !== $fieldDescription->getOption('actions')) {
            $actions = $fieldDescription->getOption('actions');
            foreach ($actions as $k => $action) {
                if (!isset($action['template'])) {
                    $actions[$k]['template'] = sprintf('SonataAdminBundle:CRUD:list__action_%s.html.twig', $k);
                }
            }

            $fieldDescription->setOption('actions', $actions);
        }

        return $fieldDescription;
    }
}
