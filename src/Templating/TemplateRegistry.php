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

/**
 * @author Timo Bakx <timobakx@gmail.com>
 */
final class TemplateRegistry implements MutableTemplateRegistryInterface
{
    /**
     * @var string[]
     */
    private $templates = [];

    /**
     * @param string[] $templates
     */
    public function __construct(array $templates = [])
    {
        $this->templates = $templates;
    }

    /**
     * @return string[]
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }

    /**
     * @param string[] $templates
     */
    public function setTemplates(array $templates): void
    {
        $this->templates = $templates;
    }

    public function hasTemplate(string $name): bool
    {
        return isset($this->templates[$name]);
    }

    public function getTemplate($name): string
    {
        if (isset($this->templates[$name])) {
            return $this->templates[$name];
        }

        throw new \InvalidArgumentException(sprintf(
            'Template named "%s" doesn\'t exist.',
            $name
        ));
    }

    public function setTemplate($name, $template): void
    {
        $this->templates[$name] = $template;
    }
}
