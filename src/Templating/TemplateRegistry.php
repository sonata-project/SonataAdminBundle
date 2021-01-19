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
 * NEXT_MAJOR: remove `MutableTemplateRegistryInterface` implementation.
 */
final class TemplateRegistry extends AbstractTemplateRegistry implements MutableTemplateRegistryInterface
{
    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since version sonata-project/admin-bundle 3.39.0 and will be removed in 4.0. Use Sonata\AdminBundle\Templating\MutableTemplateRegistry instead.
     */
    public function setTemplates(array $templates): void
    {
        $this->templates = $templates;

        @trigger_error(sprintf(
            'Method "%s()" is deprecated since sonata-project/admin-bundle 3.39 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since version sonata-project/admin-bundle 3.39.0 and will be removed in 4.0. Use Sonata\AdminBundle\Templating\MutableTemplateRegistry instead.
     */
    public function setTemplate(string $name, string $template): void
    {
        $this->templates[$name] = $template;

        @trigger_error(sprintf(
            'Method "%s()" is deprecated since sonata-project/admin-bundle 3.39 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);
    }
}
