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

use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Twig\RenderElementRuntime;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class RenderElementExtension extends AbstractExtension
{
    /**
     * NEXT_MAJOR: Remove this constructor.
     *
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private RenderElementRuntime $renderElementRuntime
    ) {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'render_list_element',
                [RenderElementRuntime::class, 'renderListElement'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
            new TwigFilter(
                'render_view_element',
                [RenderElementRuntime::class, 'renderViewElement'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
            new TwigFilter(
                'render_view_element_compare',
                [RenderElementRuntime::class, 'renderViewElementCompare'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
            new TwigFilter(
                'render_relation_element',
                [RenderElementRuntime::class, 'renderRelationElement']
            ),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use RenderElementRuntime::renderListElement() instead
     *
     * render a list element from the FieldDescription.
     *
     * @param object|mixed[]       $listElement
     * @param array<string, mixed> $params
     */
    public function renderListElement(
        Environment $environment,
        $listElement,
        FieldDescriptionInterface $fieldDescription,
        array $params = []
    ): string {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            RenderElementRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->renderElementRuntime->renderListElement($environment, $listElement, $fieldDescription, $params);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use RenderElementRuntime::renderViewElement() instead
     */
    public function renderViewElement(
        Environment $environment,
        FieldDescriptionInterface $fieldDescription,
        object $object
    ): string {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            RenderElementRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->renderElementRuntime->renderViewElement($environment, $fieldDescription, $object);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use RenderElementRuntime::renderViewElementCompare() instead
     *
     * render a compared view element.
     */
    public function renderViewElementCompare(
        Environment $environment,
        FieldDescriptionInterface $fieldDescription,
        mixed $baseObject,
        mixed $compareObject
    ): string {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            RenderElementRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->renderElementRuntime->renderViewElementCompare($environment, $fieldDescription, $baseObject, $compareObject);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use RenderElementRuntime::renderRelationElement() instead
     *
     * @throws \RuntimeException
     */
    public function renderRelationElement(mixed $element, FieldDescriptionInterface $fieldDescription): mixed
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            RenderElementRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->renderElementRuntime->renderRelationElement($element, $fieldDescription);
    }
}
