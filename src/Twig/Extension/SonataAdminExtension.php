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

use Doctrine\Common\Util\ClassUtils;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Exception\NoValueException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Template;
use Twig\TemplateWrapper;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SonataAdminExtension extends AbstractExtension
{
    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    // @todo: there are more locales which are not supported by moment and they need to be translated/normalized/canonicalized here
    public const MOMENT_UNSUPPORTED_LOCALES = [
        'de' => ['de', 'de-at'],
        'es' => ['es', 'es-do'],
        'nl' => ['nl', 'nl-be'],
        'fr' => ['fr', 'fr-ca', 'fr-ch'],
    ];

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TranslatorInterface|null
     */
    protected $translator;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var string[]
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    private $xEditableTypeMapping = [];

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var ContainerInterface
     */
    private $templateRegistries;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var AuthorizationCheckerInterface
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    private $securityChecker;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var XEditableExtension|null
     */
    private $xEditableExtension;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var SecurityExtension|null
     */
    private $securityExtension;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var CanonicalizeExtension|null
     */
    private $canonicalizeExtension;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var RenderElementExtension|null
     */
    private $renderElementExtension;

    /**
     * NEXT_MAJOR: Remove @internal tag, $translator & $securityChecker parameters and make propertyAccessor mandatory.
     *
     * @internal
     */
    public function __construct(
        Pool $pool,
        ?LoggerInterface $logger = null, //NEXT_MAJOR: Remove this parameter
        $translator = null,
        ?ContainerInterface $templateRegistries = null,
        $propertyAccessorOrSecurityChecker = null,
        ?AuthorizationCheckerInterface $securityChecker = null //NEXT_MAJOR: Remove this parameter
    ) {
        // NEXT_MAJOR: make the translator parameter required, move TranslatorInterface check to method signature
        // and remove this block

        if (null === $translator) {
            @trigger_error(
                'The $translator parameter will be required field with the 4.0 release.',
                E_USER_DEPRECATED
            );
        } else {
            if (!$translator instanceof TranslatorInterface) {
                @trigger_error(sprintf(
                    'The $translator parameter should be an instance of "%s" and will be mandatory in 4.0.',
                    TranslatorInterface::class
                ), E_USER_DEPRECATED);
            }

            if (!$translator instanceof TranslatorInterface && !$translator instanceof LegacyTranslationInterface) {
                throw new \TypeError(sprintf(
                    'Argument 2 must be an instance of "%s" or preferably "%s", "%s given"',
                    TranslatorInterface::class,
                    LegacyTranslationInterface::class,
                    \get_class($translator)
                ));
            }
        }

        // NEXT_MAJOR: Remove this block.
        if (!$propertyAccessorOrSecurityChecker instanceof PropertyAccessorInterface
            && !$propertyAccessorOrSecurityChecker instanceof AuthorizationCheckerInterface
            && null !== $propertyAccessorOrSecurityChecker
        ) {
            throw new \TypeError(sprintf(
                'Argument 5 must be an instance of "%s" or "%s" or null, %s given.',
                PropertyAccessorInterface::class,
                AuthorizationCheckerInterface::class,
                \is_object($propertyAccessorOrSecurityChecker) ? 'instance of "'.\get_class($propertyAccessorOrSecurityChecker).'"' : '"'.\gettype($propertyAccessorOrSecurityChecker).'"'
            ));
        }

        // NEXT_MAJOR: Remove this block and extract the else part outside.
        if ($propertyAccessorOrSecurityChecker instanceof AuthorizationCheckerInterface) {
            @trigger_error(sprintf(
                'Passing an instance of "%s" as argument 5 for "%s()" is deprecated since sonata-project/admin-bundle'
                .' 3.82 and will throw a \TypeError error in version 4.0. You MUST pass an instance of "%s" instead and pass'
                .' an instance of "%s" as argument 6.',
                AuthorizationCheckerInterface::class,
                __METHOD__,
                PropertyAccessorInterface::class,
                AuthorizationCheckerInterface::class
            ), E_USER_DEPRECATED);

            $this->securityChecker = $propertyAccessorOrSecurityChecker;
            $this->propertyAccessor = $pool->getPropertyAccessor();
        } elseif (null === $propertyAccessorOrSecurityChecker) {
            @trigger_error(sprintf(
                'Omitting the argument 5 for "%s()" or passing "null" is deprecated since sonata-project/admin-bundle'
                .' 3.82 and will throw a \TypeError error in version 4.0. You must pass an instance of "%s" instead.',
                __METHOD__,
                PropertyAccessorInterface::class
            ), E_USER_DEPRECATED);

            $this->propertyAccessor = $pool->getPropertyAccessor();
            $this->securityChecker = $securityChecker;
        } else {
            $this->securityChecker = $securityChecker; //NEXT_MAJOR: Remove this property
            $this->propertyAccessor = $propertyAccessorOrSecurityChecker;
        }

        $this->pool = $pool;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->templateRegistries = $templateRegistries;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            //NEXT_MAJOR remove this filter
            new TwigFilter(
                'render_list_element',
                function (
                    Environment $environment,
                    $listElement,
                    FieldDescriptionInterface $fieldDescription,
                    $params = []
                ) {
                    return $this->renderListElement(
                        $environment,
                        $listElement,
                        $fieldDescription,
                        $params,
                        'sonata_deprecation_mute'
                    );
                },
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
            //NEXT_MAJOR remove this filter
            new TwigFilter(
                'render_view_element',
                function (
                    Environment $environment,
                    FieldDescriptionInterface $fieldDescription,
                    $object
                ) {
                    return $this->renderViewElement(
                        $environment,
                        $fieldDescription,
                        $object,
                        'sonata_deprecation_mute'
                    );
                },
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
            //NEXT_MAJOR remove this filter
            new TwigFilter(
                'render_view_element_compare',
                function (
                    Environment $environment,
                    FieldDescriptionInterface $fieldDescription,
                    $baseObject,
                    $compareObject
                ) {
                    return $this->renderViewElementCompare(
                        $environment,
                        $fieldDescription,
                        $baseObject,
                        $compareObject,
                        'sonata_deprecation_mute'
                    );
                },
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
            //NEXT_MAJOR remove this filter
            new TwigFilter(
                'render_relation_element',
                function (
                    $element,
                    FieldDescriptionInterface $fieldDescription
                ) {
                    return $this->renderRelationElement(
                        $element,
                        $fieldDescription,
                        'sonata_deprecation_mute'
                    );
                }
            ),
            new TwigFilter(
                'sonata_urlsafeid',
                [$this, 'getUrlSafeIdentifier']
            ),
            //NEXT_MAJOR remove this filter
            new TwigFilter(
                'sonata_xeditable_type',
                function ($type) {
                    return $this->getXEditableType($type, 'sonata_deprecation_mute');
                }
            ),
            //NEXT_MAJOR remove this filter
            new TwigFilter(
                'sonata_xeditable_choices',
                function (FieldDescriptionInterface $fieldDescription) {
                    return $this->getXEditableChoices($fieldDescription, 'sonata_deprecation_mute');
                }
            ),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @return TwigFunction[]
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('canonicalize_locale_for_moment', function (array $context) {
                return $this->getCanonicalizedLocaleForMoment($context, 'sonata_deprecation_mute');
            }, ['needs_context' => true]),
            new TwigFunction('canonicalize_locale_for_select2', function (array $context) {
                return $this->getCanonicalizedLocaleForSelect2($context, 'sonata_deprecation_mute');
            }, ['needs_context' => true]),
            new TwigFunction('is_granted_affirmative', function ($role, $object = null, $field = null) {
                return $this->isGrantedAffirmative($role, $object, $field, 'sonata_deprecation_mute');
            }),
        ];
    }

    public function getName()
    {
        return 'sonata_admin';
    }

    /**
     * NEXT_MAJOR: Remove this method
     * render a list element from the FieldDescription.
     *
     * @param object|array $listElement
     * @param array        $params
     *
     * @return string
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    public function renderListElement(
        Environment $environment,
        $listElement,
        FieldDescriptionInterface $fieldDescription,
        $params = []
    ) {
        if ('sonata_deprecation_mute' !== (\func_get_args()[4] ?? null)) {
            @trigger_error(sprintf(
                'The %s method is deprecated in favor of RenderElementExtension::renderListElement since version 3.87 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        if (null === $this->renderElementExtension) {
            $this->renderElementExtension = new RenderElementExtension($this->propertyAccessor, $this->templateRegistries, $this->logger);
        }

        return $this->renderElementExtension->renderListElement($environment, $listElement, $fieldDescription, $params);
    }

    /**
     * @deprecated since sonata-project/admin-bundle 3.33, to be removed in 4.0. Use render instead
     *
     * @return string
     */
    public function output(
        FieldDescriptionInterface $fieldDescription,
        Template $template,
        array $parameters,
        Environment $environment
    ) {
        @trigger_error(sprintf(
            'The %s method is deprecated since version 3.33 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

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
                $fieldDescription->getTemplate(),
                $template->getSourceContext()->getName(),
                $content,
                $fieldDescription->getFieldName()
            );
        }

        return $content;
    }

    /**
     * return the value related to FieldDescription, if the associated object does no
     * exists => a temporary one is created.
     *
     * @param object $object
     *
     * @throws \RuntimeException
     *
     * NEXT_MAJOR: Remove this method
     *
     * @deprecated This method is deprecated with no replacement since sonata-project/admin-bundle 3.73 and will be removed in 4.0.
     *
     * @return mixed
     */
    public function getValueFromFieldDescription(
        $object,
        FieldDescriptionInterface $fieldDescription,
        array $params = []
    ) {
        @trigger_error(sprintf(
            'The %s() method is deprecated since sonata-project/admin-bundle 3.73 and will be removed in version 4.0.'
            .' There is no replacement.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (isset($params['loop']) && $object instanceof \ArrayAccess) {
            throw new \RuntimeException('remove the loop requirement');
        }

        $value = null;

        try {
            $value = $fieldDescription->getValue($object);
        } catch (NoValueException $e) {
            if ($fieldDescription->getAssociationAdmin()) {
                $value = $fieldDescription->getAssociationAdmin()->getNewInstance();
            } else {
                // NEXT_MAJOR: throw the NoValueException.
                @trigger_error(
                    sprintf(
                        'Accessing a non existing value for the field "%s" is deprecated'
                        .' since sonata-project/admin-bundle 3.67 and will throw an exception in 4.0.',
                        $fieldDescription->getName(),
                    ),
                    E_USER_DEPRECATED
                );
            }
        }

        return $value;
    }

    /**
     * NEXT_MAJOR: Remove this method
     * render a view element.
     *
     * @param object $object
     *
     * @return string
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    public function renderViewElement(
        Environment $environment,
        FieldDescriptionInterface $fieldDescription,
        $object
    ) {
        if ('sonata_deprecation_mute' !== (\func_get_args()[3] ?? null)) {
            @trigger_error(sprintf(
                'The %s method is deprecated in favor of RenderElementExtension::renderViewElement since version 3.87 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        if (null === $this->renderElementExtension) {
            $this->renderElementExtension = new RenderElementExtension($this->propertyAccessor, $this->templateRegistries, $this->logger);
        }

        return $this->renderElementExtension->renderViewElement($environment, $fieldDescription, $object);
    }

    /**
     * NEXT_MAJOR: Remove this method
     * render a compared view element.
     *
     * @param mixed $baseObject
     * @param mixed $compareObject
     *
     * @return string
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    public function renderViewElementCompare(
        Environment $environment,
        FieldDescriptionInterface $fieldDescription,
        $baseObject,
        $compareObject
    ) {
        if ('sonata_deprecation_mute' !== (\func_get_args()[4] ?? null)) {
            @trigger_error(sprintf(
                'The %s method is deprecated in favor of RenderElementExtension::renderViewElementCompare since version 3.87 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }
        if (null === $this->renderElementExtension) {
            $this->renderElementExtension = new RenderElementExtension($this->propertyAccessor, $this->templateRegistries, $this->logger);
        }

        return $this->renderElementExtension->renderViewElementCompare($environment, $fieldDescription, $baseObject, $compareObject);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param mixed $element
     *
     * @throws \RuntimeException
     *
     * @return mixed
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    public function renderRelationElement($element, FieldDescriptionInterface $fieldDescription)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[2] ?? null)) {
            @trigger_error(sprintf(
                'The %s method is deprecated in favor of RenderElementExtension::renderRelationElement since version 3.87 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        if (null === $this->renderElementExtension) {
            $this->renderElementExtension = new RenderElementExtension($this->propertyAccessor, $this->templateRegistries, $this->logger);
        }

        return $this->renderElementExtension->renderRelationElement($element, $fieldDescription);
    }

    /**
     * Get the identifiers as a string that is safe to use in a url.
     *
     * @param object $model
     *
     * @return string string representation of the id that is safe to use in a url
     */
    public function getUrlSafeIdentifier($model, ?AdminInterface $admin = null)
    {
        if (null === $admin) {
            $class = ClassUtils::getClass($model);
            if (!$this->pool->hasAdminByClass($class)) {
                throw new \InvalidArgumentException('You must pass an admin.');
            }

            $admin = $this->pool->getAdminByClass($class);
        }

        return $admin->getUrlSafeIdentifier($model);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param string[] $xEditableTypeMapping
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    public function setXEditableTypeMapping($xEditableTypeMapping)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'The %s method is deprecated in favor of XEditableExtension::setXEditableTypeMapping since version 3.87 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        $this->xEditableTypeMapping = $xEditableTypeMapping;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @return string|bool
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    public function getXEditableType($type)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'The %s method is deprecated in favor of XEditableExtension::getXEditableType since version 3.87 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        return $this->xEditableTypeMapping[$type] ?? false;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * Return xEditable choices based on the field description choices options & catalogue options.
     * With the following choice options:
     *     ['Status1' => 'Alias1', 'Status2' => 'Alias2']
     * The method will return:
     *     [['value' => 'Status1', 'text' => 'Alias1'], ['value' => 'Status2', 'text' => 'Alias2']].
     *
     * @return array
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    public function getXEditableChoices(FieldDescriptionInterface $fieldDescription)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'The %s method is deprecated in favor of XEditableExtension::getXEditableChoices since version 3.87 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        if (null === $this->xEditableExtension) {
            $this->xEditableExtension = new XEditableExtension($this->translator, $this->xEditableTypeMapping);
        }

        return $this->xEditableExtension->getXEditableChoices($fieldDescription);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * Returns a canonicalized locale for "moment" NPM library,
     * or `null` if the locale's language is "en", which doesn't require localization.
     *
     * @return string|null
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    final public function getCanonicalizedLocaleForMoment(array $context)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'The %s method is deprecated in favor of CanonicalizeExtension::getCanonicalizedLocaleForMoment since version 3.87 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        if (null === $this->canonicalizeExtension) {
            $requestStack = new RequestStack();
            $requestStack->push($context['app']->getRequest());
            $this->canonicalizeExtension = new CanonicalizeExtension($requestStack);
        }

        return $this->canonicalizeExtension->getCanonicalizedLocaleForMoment();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * Returns a canonicalized locale for "select2" NPM library,
     * or `null` if the locale's language is "en", which doesn't require localization.
     *
     * @return string|null
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    final public function getCanonicalizedLocaleForSelect2(array $context)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'The %s method is deprecated in favor of CanonicalizeExtension::getCanonicalizedLocaleForSelect2 since version 3.87 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        if (null === $this->canonicalizeExtension) {
            $requestStack = new RequestStack();
            $requestStack->push($context['app']->getRequest());
            $this->canonicalizeExtension = new CanonicalizeExtension($requestStack);
        }

        return $this->canonicalizeExtension->getCanonicalizedLocaleForSelect2();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param string|array $role
     * @param object|null  $object
     * @param string|null  $field
     *
     * @return bool
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    public function isGrantedAffirmative($role, $object = null, $field = null)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[3] ?? null)) {
            @trigger_error(sprintf(
                'The %s method is deprecated in favor of SecurityExtension::isGrantedAffirmative since version 3.87 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        if (null === $this->securityExtension) {
            $this->securityExtension = new SecurityExtension($this->securityChecker);
        }

        return $this->securityExtension->isGrantedAffirmative($role, $object, $field);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * Get template.
     *
     * @param string $defaultTemplate
     *
     * @return TemplateWrapper
     *
     * @deprecated since sonata-project/admin-bundle 3.87 and will be removed in 4.0
     */
    protected function getTemplate(
        FieldDescriptionInterface $fieldDescription,
        $defaultTemplate,
        Environment $environment
    ) {
        if ('sonata_deprecation_mute' !== (\func_get_args()[3] ?? null)) {
            @trigger_error(sprintf(
                'The %s method is deprecated in favor of RenderElementExtension::getTemplate since version 3.87 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        return $this->renderElementExtension->getTemplate($fieldDescription, $defaultTemplate, $$environment);
    }
}
