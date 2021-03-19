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
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface ShowBuilderInterface extends BuilderInterface
{
    /**
     * @param array<string, mixed> $options
     *
     * @return FieldDescriptionCollection<FieldDescriptionInterface>
     */
    public function getBaseList(array $options = []);

    /**
     * @param FieldDescriptionCollection<FieldDescriptionInterface> $list
     * @param string|null                                           $type
     * @param AdminInterface<object>                                $admin
     */
    public function addField(
        FieldDescriptionCollection $list,
        $type,
        FieldDescriptionInterface $fieldDescription,
        AdminInterface $admin
    );
}
