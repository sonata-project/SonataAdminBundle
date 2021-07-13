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
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class BreadcrumbsExtension extends AbstractExtension
{
    /**
     * @var BreadcrumbsBuilderInterface
     */
    private $breadcrumbsBuilder;

    /**
     * @internal This class should only be used through Twig
     */
    public function __construct(BreadcrumbsBuilderInterface $breadcrumbsBuilder)
    {
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_breadcrumbs', [$this, 'renderBreadcrumbs'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
            new TwigFunction('render_breadcrumbs_for_title', [$this, 'renderBreadcrumbsForTitle'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
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
        string $action
    ): string {
        return $environment->render('@SonataAdmin/Breadcrumb/breadcrumb.html.twig', [
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
        string $action
    ): string {
        return $environment->render('@SonataAdmin/Breadcrumb/breadcrumb_title.html.twig', [
            'items' => $this->breadcrumbsBuilder->getBreadcrumbs($admin, $action),
        ]);
    }
}
