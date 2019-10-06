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
 *
 * @method bool hasTemplate(string $name)
 */
interface TemplateRegistryInterface
{
    /**
     * @return array 'name' => 'file_path.html.twig'
     */
    public function getTemplates();

    /**
     * @param string $name
     *
     * @return string
     */
    public function getTemplate($name);

    // NEXT_MAJOR: Uncomment the following method
    // public function hasTemplate(string $name): bool;
}
