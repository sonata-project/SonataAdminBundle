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

namespace SensioLabs\AdminBundle\Templating;

/**
 * @author Wojciech Błoszyk <wbloszyk@gmail.com>
 */
interface MutableTemplateRegistryAwareInterface
{
    public function getTemplateRegistry(): MutableTemplateRegistryInterface;

    public function setTemplateRegistry(MutableTemplateRegistryInterface $templateRegistry): void;

    public function hasTemplateRegistry(): bool;

    public function setTemplate(string $name, string $template): void;

    /**
     * @param array<string, string> $templates
     */
    public function setTemplates(array $templates): void;
}
