<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\App\Builder;

use SensioLabs\AdminBundle\Builder\ShowBuilderInterface;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionCollection;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\Templating\TemplateRegistryInterface;

final class ShowBuilder implements ShowBuilderInterface
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

    public function addField(FieldDescriptionCollection $list, ?string $type, FieldDescriptionInterface $fieldDescription): void
    {
        $fieldDescription->setType($type);
        $this->fixFieldDescription($fieldDescription);

        $list->add($fieldDescription);
    }

    private function getTemplate(?string $type): ?string
    {
        if (null === $type) {
            return null;
        }

        return TemplateRegistryInterface::SHOW_TEMPLATES[$type] ?? null;
    }
}
