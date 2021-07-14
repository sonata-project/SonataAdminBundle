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
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TemplateWrapper;
use Twig\TwigFilter;

final class RenderElementExtension extends AbstractExtension
{
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @internal This class should only be used through Twig
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'render_list_element',
                [$this, 'renderListElement'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
            new TwigFilter(
                'render_view_element',
                [$this, 'renderViewElement'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
            new TwigFilter(
                'render_view_element_compare',
                [$this, 'renderViewElementCompare'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
            new TwigFilter(
                'render_relation_element',
                [$this, 'renderRelationElement']
            ),
        ];
    }

    /**
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
        $template = $this->getTemplate(
            $fieldDescription,
            $fieldDescription->getAdmin()->getTemplateRegistry()->getTemplate('base_list_field'),
            $environment
        );

        [$object, $value] = $this->getObjectAndValueFromListElement($listElement, $fieldDescription);

        return $this->render($fieldDescription, $template, array_merge($params, [
            'admin' => $fieldDescription->getAdmin(),
            'object' => $object,
            'value' => $value,
            'field_description' => $fieldDescription,
        ]), $environment);
    }

    public function renderViewElement(
        Environment $environment,
        FieldDescriptionInterface $fieldDescription,
        object $object
    ): string {
        $template = $this->getTemplate(
            $fieldDescription,
            '@SonataAdmin/CRUD/base_show_field.html.twig',
            $environment
        );

        return $this->render($fieldDescription, $template, [
            'field_description' => $fieldDescription,
            'object' => $object,
            'value' => $fieldDescription->getValue($object),
            'admin' => $fieldDescription->getAdmin(),
        ], $environment);
    }

    /**
     * render a compared view element.
     *
     * @param mixed $baseObject
     * @param mixed $compareObject
     */
    public function renderViewElementCompare(
        Environment $environment,
        FieldDescriptionInterface $fieldDescription,
        $baseObject,
        $compareObject
    ): string {
        $template = $this->getTemplate(
            $fieldDescription,
            '@SonataAdmin/CRUD/base_show_field.html.twig',
            $environment
        );

        $baseValue = $fieldDescription->getValue($baseObject);
        $compareValue = $fieldDescription->getValue($compareObject);

        $baseValueOutput = $template->render([
            'admin' => $fieldDescription->getAdmin(),
            'field_description' => $fieldDescription,
            'value' => $baseValue,
            'object' => $baseObject,
        ]);

        $compareValueOutput = $template->render([
            'field_description' => $fieldDescription,
            'admin' => $fieldDescription->getAdmin(),
            'value' => $compareValue,
            'object' => $compareObject,
        ]);

        // Compare the rendered output of both objects by using the (possibly) overridden field block
        $isDiff = $baseValueOutput !== $compareValueOutput;

        return $this->render($fieldDescription, $template, [
            'field_description' => $fieldDescription,
            'value' => $baseValue,
            'value_compare' => $compareValue,
            'is_diff' => $isDiff,
            'admin' => $fieldDescription->getAdmin(),
            'object' => $baseObject,
            'object_compare' => $compareObject,
        ], $environment);
    }

    /**
     * @param mixed $element
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function renderRelationElement($element, FieldDescriptionInterface $fieldDescription)
    {
        if (!\is_object($element)) {
            return $element;
        }

        $propertyPath = $fieldDescription->getOption('associated_property');

        if (null === $propertyPath) {
            if (!method_exists($element, '__toString')) {
                throw new \RuntimeException(sprintf(
                    'You must define an `associated_property` option or create a `%s::__toString` method'
                    .' to the field option %s from service %s is ',
                    \get_class($element),
                    $fieldDescription->getName(),
                    $fieldDescription->getAdmin()->getCode()
                ));
            }

            return $element->__toString();
        }

        if (\is_callable($propertyPath)) {
            return $propertyPath($element);
        }

        return $this->propertyAccessor->getValue($element, $propertyPath);
    }

    /**
     * Extracts the object and requested value from the $listElement.
     *
     * @param object|mixed[] $listElement
     *
     * @throws \TypeError when $listElement is not an object or an array with an object on offset 0
     *
     * @return mixed[] An array containing object and value
     *
     * @phpstan-return array{0: object, 1: mixed}
     */
    private function getObjectAndValueFromListElement(
        $listElement,
        FieldDescriptionInterface $fieldDescription
    ): array {
        if (\is_object($listElement)) {
            $object = $listElement;
        } elseif (\is_array($listElement)) {
            if (!isset($listElement[0]) || !\is_object($listElement[0])) {
                throw new \TypeError(sprintf('If argument 1 passed to %s() is an array it must contain an object at offset 0.', __METHOD__));
            }

            $object = $listElement[0];
        } else {
            throw new \TypeError(sprintf('Argument 1 passed to %s() must be an object or an array, %s given.', __METHOD__, \gettype($listElement)));
        }

        if (\is_array($listElement) && \array_key_exists($fieldDescription->getName(), $listElement)) {
            $value = $listElement[$fieldDescription->getName()];
        } else {
            $value = $fieldDescription->getValue($object);
        }

        return [$object, $value];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function render(
        FieldDescriptionInterface $fieldDescription,
        TemplateWrapper $template,
        array $parameters,
        Environment $environment
    ): string {
        $content = $template->render($parameters);

        if ($environment->isDebug()) {
            $commentTemplate = <<<'EOT'

<!-- START
    fieldName: %s
    template: %s
    compiled template: %s
    -->
    %s
<!-- END - fieldName: %s -->
EOT;

            return sprintf(
                $commentTemplate,
                $fieldDescription->getFieldName(),
                $fieldDescription->getTemplate() ?? '',
                $template->getSourceContext()->getName(),
                $content,
                $fieldDescription->getFieldName()
            );
        }

        return $content;
    }

    private function getTemplate(
        FieldDescriptionInterface $fieldDescription,
        string $defaultTemplate,
        Environment $environment
    ): TemplateWrapper {
        $templateName = $fieldDescription->getTemplate() ?? $defaultTemplate;

        return $environment->load($templateName);
    }
}
