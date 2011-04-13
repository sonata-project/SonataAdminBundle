<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\ModelManager\Mandango\Builder;

use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListCollection;

/**
 * MandangoListBuilder.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MandangoListBuilder implements ListBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBaseList(array $options = array())
    {
        return new ListCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function addField(ListCollection $list, FieldDescriptionInterface $fieldDescription)
    {
        $list->add($fieldDescription);
    }

    /**
     * {@inheritdoc}
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription, array $options = array())
    {
        $fieldDescription->mergeOptions($options);
        $fieldDescription->setAdmin($admin);

        if (!$fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate(sprintf('SonataAdminBundle:CRUD:list_%s.html.twig', $fieldDescription->getType()));
        }
    }
}
