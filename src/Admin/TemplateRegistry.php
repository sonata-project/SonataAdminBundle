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

namespace Sonata\AdminBundle\Admin;

class TemplateRegistry implements TemplateRegistryInterface
{
    private $templates = [];

    public function __construct(array $templates)
    {
        $this->templates = $templates;
    }

    public function setTemplate(string $name, string $template): void
    {
        $this->templates[$name] = $template;
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }

    public function getTemplate(string $name): string
    {
        if ($this->hasTemplate($name)) {
            return $this->templates[$name];
        }

        throw new \InvalidArgumentException(sprintf('Template "%s" not found in template registry', $name));
    }

    public function hasTemplate(string $name): bool
    {
        return isset($this->templates[$name]);
    }
}
