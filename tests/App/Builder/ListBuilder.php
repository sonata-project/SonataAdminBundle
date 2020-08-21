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

namespace Sonata\AdminBundle\Tests\App\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionCollectionInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;

final class ListBuilder implements ListBuilderInterface
{
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription): void
    {
    }

    public function getBaseList(array $options = []): FieldDescriptionCollectionInterface
    {
        return new FieldDescriptionCollection();
    }

    public function buildField($type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin): void
    {
    }

    public function addField(FieldDescriptionCollectionInterface $list, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin): void
    {
        $fieldDescription->setType($type);
        $admin->addListFieldDescription($fieldDescription->getName(), $fieldDescription);
        $fieldDescription->setAdmin($admin);

        $list->add($fieldDescription);
    }
}
