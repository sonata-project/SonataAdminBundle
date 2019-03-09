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

interface TemplateRegistryInterface
{
    /**
     * Sets a specific template.
     */
    public function setTemplate(string $name, string $template): void;

    /**
     * Get all templates.
     */
    public function getTemplates(): array;

    /**
     * Returns template.
     *
     * @throws \InvalidArgumentException
     */
    public function getTemplate(string $name): string;

    /**
     * Return true if the template is set.
     */
    public function hasTemplate(string $name): bool;
}
