<?php

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
     * @param array $templates 'name' => 'file_path.html.twig'
     */
    public function setTemplates(array $templates);

    /**
     * @param string $name
     * @param string $template
     */
    public function setTemplate($name, $template);
}
