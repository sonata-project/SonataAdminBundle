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
     * @deprecated since sonata-project/admin-bundle x.y, will be removed in 5.0.
     *
     * @var array<string, string> 'name' => 'file_path.html.twig'
     */
    protected $templates = [];

    /**
     * @var array<string, array<string, string>> 'layout' => ['name' => 'file_path.html.twig']
     */
    protected array $layoutTemplates = [];

    /**
     * @param string[] $templates
     */
    public function __construct(array $templates = [])
    {
        $this->templates = $templates;
        $this->layoutTemplates['default'] = $templates;
    }

    final public function getTemplates(/* string $layout */): array
    {
        if (\func_num_args() < 1) {
            @trigger_error(
                'Not passing the "string $layout" argument explicitly is deprecated since sonata-project/admin-bundle x.y and will be required in 5.0.',
                \E_USER_DEPRECATED
            );

            $layout = 'default';
        } else {
            $layout = func_get_arg(0);
        }

        // Keep BC compatibility
        if ('default' === $layout) {
            return $this->templates;
        }

        // NEXT_MAJOR: Remove code before this comment

        return $this->layoutTemplates[$layout] + $this->layoutTemplates['default'];
    }

    final public function hasTemplate(string $name /* ,string $layout */): bool
    {
        if (\func_num_args() < 2) {
            @trigger_error(
                'Not passing the "string $layout" argument explicitly is deprecated since sonata-project/admin-bundle x.y and will be required in 5.0.',
                \E_USER_DEPRECATED
            );

            $layout = 'default';
        } else {
            $layout = func_get_arg(1);
        }

        // Keep BC compatibility
        if ('default' === $layout) {
            return isset($this->templates[$name]);
        }

        // NEXT_MAJOR: Remove code before this comment

        return isset($this->layoutTemplates[$layout][$name]) || isset($this->layoutTemplates['default'][$name]);
    }

    final public function getTemplate(string $name /* ,string $layout */): string
    {
        if (\func_num_args() < 2) {
            @trigger_error(
                'Not passing the "string $layout" argument explicitly is deprecated since sonata-project/admin-bundle x.y and will be required in 5.0.',
                \E_USER_DEPRECATED
            );

            $layout = 'default';
        } else {
            $layout = func_get_arg(1);
        }

        // Keep BC compatibility
        if ('default' === $layout) {
            if ($this->hasTemplate($name)) {
                return $this->templates[$name];
            }

            throw new \InvalidArgumentException(sprintf('Template named "%s" doesn\'t exist.', $name));
        }

        // NEXT_MAJOR: Remove code before this comment
        if ($this->hasTemplate($name, $layout)) {
            return $this->layoutTemplates[$layout][$name] ?? $this->layoutTemplates['default'][$name];
        }

        throw new \InvalidArgumentException(sprintf('Template named "%s" for "%s" layout doesn\'t exist.', $name, $layout));
    }
}
