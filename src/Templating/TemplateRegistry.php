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
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    public function hasTemplate(string $name): bool
    {
        return isset($this->templates[$name]);
    }

    /**
     * @param string $name
     */
    public function getTemplate($name): ?string
    {
        if (isset($this->templates[$name])) {
            return $this->templates[$name];
        }

        @trigger_error(sprintf(
            'Passing a nonexistent template name as argument 1 to %s() is deprecated since sonata-project/admin-bundle 3.52 and will throw an exception in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and declare string as return type
        // throw new \InvalidArgumentException(sprintf(
        //    'Template named "%s" doesn\'t exist.',
        //    $name
        // ));

        return null;
    }

    public function setTemplate($name, $template)
    {
        $this->templates[$name] = $template;
    }
}
