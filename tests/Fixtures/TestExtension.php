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

namespace Sonata\AdminBundle\Tests\Fixtures;

use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\FormTypeExtensionInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\FormTypeInterface;

final class TestExtension implements FormExtensionInterface
{
    /**
     * @var array<string, FormTypeInterface>
     */
    private array $types = [];

    /**
     * @var array<string, array<FormTypeExtensionInterface>>
     */
    private array $extensions = [];

    public function __construct(
        private ?FormTypeGuesserInterface $guesser,
    ) {
    }

    public function addType(FormTypeInterface $type): void
    {
        $this->types[$type::class] = $type;
    }

    public function getType($name): FormTypeInterface
    {
        if (!isset($this->types[$name])) {
            throw new InvalidArgumentException(\sprintf('Type "%s" is not supported.', $name));
        }

        return $this->types[$name];
    }

    public function hasType($name): bool
    {
        return isset($this->types[$name]);
    }

    public function addTypeExtension(FormTypeExtensionInterface $extension): void
    {
        foreach ($extension::getExtendedTypes() as $type) {
            if (!isset($this->extensions[$type])) {
                $this->extensions[$type] = [];
            }

            $this->extensions[$type][] = $extension;
        }
    }

    public function getTypeExtensions($name): array
    {
        return $this->extensions[$name] ?? [];
    }

    public function hasTypeExtensions($name): bool
    {
        return isset($this->extensions[$name]);
    }

    public function getTypeGuesser(): ?FormTypeGuesserInterface
    {
        return $this->guesser;
    }
}
