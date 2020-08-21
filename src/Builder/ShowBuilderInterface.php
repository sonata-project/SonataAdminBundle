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

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollectionInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface ShowBuilderInterface extends BuilderInterface
{
    public function getBaseList(array $options = []): FieldDescriptionCollectionInterface;

    public function addField(
        FieldDescriptionCollectionInterface $list,
        ?string $type,
        FieldDescriptionInterface $fieldDescription,
        AdminInterface $admin
    ): void;
}
