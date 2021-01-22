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
interface TemplateRegistryInterface
{
    /**
     * @return array<string, string> 'name' => 'file_path.html.twig'
     */
    public function getTemplates(): array;

    public function getTemplate(string $name): string;

    public function hasTemplate(string $name): bool;
}
