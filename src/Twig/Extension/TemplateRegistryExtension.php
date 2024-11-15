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

namespace Sonata\AdminBundle\Twig\Extension;

use Sonata\AdminBundle\Twig\TemplateRegistryRuntime;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TemplateRegistryExtension extends AbstractExtension
{
    /**
     * NEXT_MAJOR: Remove this constructor.
     *
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private TemplateRegistryRuntime $templateRegistryRuntime,
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_admin_template', [TemplateRegistryRuntime::class, 'getAdminTemplate']),
            new TwigFunction('get_global_template', [TemplateRegistryRuntime::class, 'getGlobalTemplate']),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use TemplateRegistryRuntime::getAdminTemplate() instead
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function getAdminTemplate(string $name, string $adminCode): string
    {
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            TemplateRegistryRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->templateRegistryRuntime->getAdminTemplate($name, $adminCode);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use TemplateRegistryRuntime::getGlobalTemplate() instead
     */
    public function getGlobalTemplate(string $name): string
    {
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            TemplateRegistryRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->templateRegistryRuntime->getGlobalTemplate($name);
    }
}
