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
final class TemplateRegistry implements MutableTemplateRegistryInterface
{
    private $templates = [];

    public function __construct(array $templates = [])
    {
        $this->templates = $templates;
    }

    public function getTemplates()
    {
        return $this->templates;
    }

    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    public function getTemplate($name)
    {
        if (isset($this->templates[$name])) {
            return $this->templates[$name];
        }
    }

    public function setTemplate($name, $template)
    {
        $this->templates[$name] = $template;
    }
}
