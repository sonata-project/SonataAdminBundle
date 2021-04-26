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
     * @internal
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

    // NEXT_MAJOR: Remove $breadcrumbsBuilder parameter;
    public function renderBreadcrumbs(
        Environment $environment,
        AdminInterface $admin,
        string $action,
        ?BreadcrumbsBuilderInterface $breadcrumbsBuilderForBC = null
    ): string {
        // NEXT_MAJOR: Remove next line.
        $breadcrumbsBuilder = $this->breadcrumbsBuilder;

        // NEXT_MAJOR: Remove the entire if block.
        if (null !== $breadcrumbsBuilderForBC && \get_class($breadcrumbsBuilderForBC) !== \get_class($this->breadcrumbsBuilder)) {
            @trigger_error(
                'Overriding "breadcrumbs_builder" parameter in twig templates is deprecated since'
                .' sonata-project/admin-bundle version 3.x and this parameter will be removed in 4.0.'
                .' Use "sonata.admin.breadcrumbs_builder" service instead.',
                \E_USER_DEPRECATED
            );

            $breadcrumbsBuilder = $breadcrumbsBuilderForBC;
        }

        return $environment->render('@SonataAdmin/Breadcrumb/breadcrumb.html.twig', [
            // NEXT_MAJOR: Use $this->breadcrumbsBuilder.
            'items' => $breadcrumbsBuilder->getBreadcrumbs($admin, $action),
        ]);
    }

    // NEXT_MAJOR: Remove $breadcrumbsBuilder parameter;
    public function renderBreadcrumbsForTitle(
        Environment $environment,
        AdminInterface $admin,
        string $action,
        ?BreadcrumbsBuilderInterface $breadcrumbsBuilderForBC = null
    ): string {
        // NEXT_MAJOR: Remove next line.
        $breadcrumbsBuilder = $this->breadcrumbsBuilder;

        // NEXT_MAJOR: Remove the entire if block.
        if (null !== $breadcrumbsBuilderForBC && \get_class($breadcrumbsBuilderForBC) !== \get_class($this->breadcrumbsBuilder)) {
            @trigger_error(
                'Overriding "breadcrumbs_builder" parameter in twig templates is deprecated since'
                .' sonata-project/admin-bundle version 3.x and this parameter will be removed in 4.0.'
                .' Use "sonata.admin.breadcrumbs_builder" service instead.',
                \E_USER_DEPRECATED
            );

            $breadcrumbsBuilder = $breadcrumbsBuilderForBC;
        }

        return $environment->render('@SonataAdmin/Breadcrumb/breadcrumb_title.html.twig', [
            // NEXT_MAJOR: Use $this->breadcrumbsBuilder.
            'items' => $breadcrumbsBuilder->getBreadcrumbs($admin, $action),
        ]);
    }
}
