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

use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;

final class ListBuilder implements ListBuilderInterface
{
    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        if (null === $fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate($this->getTemplate($fieldDescription->getType()));
        }
    }

    public function getBaseList(array $options = []): FieldDescriptionCollection
    {
        return new FieldDescriptionCollection();
    }

    public function buildField(?string $type, FieldDescriptionInterface $fieldDescription): void
    {
        $fieldDescription->setType($type);
        $this->fixFieldDescription($fieldDescription);
    }

    public function addField(FieldDescriptionCollection $list, ?string $type, FieldDescriptionInterface $fieldDescription): void
    {
        $this->buildField($type, $fieldDescription);
        $fieldDescription->getAdmin()->addListFieldDescription($fieldDescription->getName(), $fieldDescription);

        $list->add($fieldDescription);
    }

    private function getTemplate(?string $type): ?string
    {
        return TemplateRegistryInterface::LIST_TEMPLATES[$type] ?? null;
    }
}
