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

namespace Sonata\AdminBundle\Templating;

abstract class AbstractTemplateRegistry implements TemplateRegistryInterface
{
    /**
     * @var array<string, string>
     */
    protected $templates = [];

    /**
     * @param string[] $templates
     */
    public function __construct(array $templates = [])
    {
        $this->templates = $templates;
    }

    final public function getTemplates(): array
    {
        return $this->templates;
    }

    final public function hasTemplate(string $name): bool
    {
        return isset($this->templates[$name]);
    }

    final public function getTemplate(string $name): string
    {
        if ($this->hasTemplate($name)) {
            return $this->templates[$name];
        }

        throw new \InvalidArgumentException(sprintf('Template named "%s" doesn\'t exist.', $name));
    }
}
