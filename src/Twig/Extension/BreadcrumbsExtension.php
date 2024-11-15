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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Twig\BreadcrumbsRuntime;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class BreadcrumbsExtension extends AbstractExtension
{
    /**
     * NEXT_MAJOR: Remove this constructor.
     *
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private BreadcrumbsRuntime $breadcrumbsRuntime,
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_breadcrumbs', [BreadcrumbsRuntime::class, 'renderBreadcrumbs'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
            new TwigFunction('render_breadcrumbs_for_title', [BreadcrumbsRuntime::class, 'renderBreadcrumbsForTitle'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use BreadcrumbsRuntime::renderBreadcrumbs() instead
     *
     * @param AdminInterface<object> $admin
     *
     * @phpstan-template T of object
     * @phpstan-param AdminInterface<T> $admin
     */
    public function renderBreadcrumbs(
        Environment $environment,
        AdminInterface $admin,
        string $action,
    ): string {
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .' Use "%s::%s()" instead.',
            __METHOD__,
            BreadcrumbsRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->breadcrumbsRuntime->renderBreadcrumbs($environment, $admin, $action);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use BreadcrumbsRuntime::renderBreadcrumbsForTitle() instead
     *
     * @param AdminInterface<object> $admin
     *
     * @phpstan-template T of object
     * @phpstan-param AdminInterface<T> $admin
     */
    public function renderBreadcrumbsForTitle(
        Environment $environment,
        AdminInterface $admin,
        string $action,
    ): string {
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            BreadcrumbsRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->breadcrumbsRuntime->renderBreadcrumbsForTitle($environment, $admin, $action);
    }
}
