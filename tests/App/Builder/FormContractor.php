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

use Sonata\AdminBundle\Builder\AbstractFormContractor;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

final class FormContractor extends AbstractFormContractor
{
    protected function hasAssociation(FieldDescriptionInterface $fieldDescription): bool
    {
        return false;
    }

    protected function hasSingleValueAssociation(FieldDescriptionInterface $fieldDescription): bool
    {
        return false;
    }
}
