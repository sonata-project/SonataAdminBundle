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
interface MutableTemplateRegistryInterface extends TemplateRegistryInterface
{
    /**
     * @param array<string, string> $templates 'name' => 'file_path.html.twig'
     */
    public function setTemplates(array $templates): void;

    public function setTemplate(string $name, string $template): void;
}
