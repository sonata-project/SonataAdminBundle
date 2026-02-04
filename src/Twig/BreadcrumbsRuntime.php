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

namespace SensioLabs\AdminBundle\Twig;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

final class BreadcrumbsRuntime implements RuntimeExtensionInterface
{
    /**
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private BreadcrumbsBuilderInterface $breadcrumbsBuilder,
    ) {
    }

    /**
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
        return $environment->render('@SensioLabsAdmin/Breadcrumb/breadcrumb.html.twig', [
            'items' => $this->breadcrumbsBuilder->getBreadcrumbs($admin, $action),
        ]);
    }

    /**
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
        return $environment->render('@SensioLabsAdmin/Breadcrumb/breadcrumb_title.html.twig', [
            'items' => $this->breadcrumbsBuilder->getBreadcrumbs($admin, $action),
        ]);
    }
}
