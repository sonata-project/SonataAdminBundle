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

namespace SensioLabs\AdminBundle\Builder\ORM;

use SensioLabs\AdminBundle\Builder\ShowBuilderInterface;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionCollection;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\FieldDescription\TypeGuesserInterface;

final class ShowBuilder implements ShowBuilderInterface
{
    /**
     * @param string[] $templates
     */
    public function __construct(
        private TypeGuesserInterface $guesser,
        private array $templates,
    ) {
    }

    public function getBaseList(array $options = []): FieldDescriptionCollection
    {
        return new FieldDescriptionCollection();
    }

    public function addField(FieldDescriptionCollection $list, ?string $type, FieldDescriptionInterface $fieldDescription): void
    {
        if (null === $type) {
            $guessType = $this->guesser->guess($fieldDescription);
            if (null === $guessType) {
                throw new \InvalidArgumentException(\sprintf(
                    'Cannot guess a type for the field description "%s", You MUST provide a type.',
                    $fieldDescription->getName()
                ));
            }

            $fieldDescription->setType($guessType->getType());
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($fieldDescription);
        $fieldDescription->getAdmin()->addShowFieldDescription($fieldDescription->getName(), $fieldDescription);

        $list->add($fieldDescription);
    }

    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        $type = $fieldDescription->getType();
        if (null === $type) {
            throw new \RuntimeException(\sprintf(
                'Please define a type for field `%s` in `%s`',
                $fieldDescription->getName(),
                $fieldDescription->getAdmin()::class
            ));
        }

        if (null === $fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate($this->getTemplate($type));
        }

        if ($fieldDescription->describesAssociation()) {
            $fieldDescription->getAdmin()->attachAdminClass($fieldDescription);
        }
    }

    private function getTemplate(string $type): ?string
    {
        if (!isset($this->templates[$type])) {
            return null;
        }

        return $this->templates[$type];
    }
}
