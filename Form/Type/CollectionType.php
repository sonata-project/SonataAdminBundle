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

namespace Sonata\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

/**
 * This type wrap native `collection` form type and render `add` and `delete`
 * buttons in standard Symfony` collection form type.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class CollectionType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'collection';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_native_collection';
    }
}
