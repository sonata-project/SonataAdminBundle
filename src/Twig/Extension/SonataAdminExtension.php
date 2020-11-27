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
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TranslatorInterface|null
     */
    protected $translator;

    /**
     * @var string[]
     */
    private $xEditableTypeMapping = [];

    /**
     * @var ContainerInterface
     */
    private $templateRegistries;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $securityChecker;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * NEXT_MAJOR: Make $propertyAccessor mandatory.
     */
    public function __construct(
        Pool $pool,
        ?LoggerInterface $logger = null,
        $translator = null,
        ?ContainerInterface $templateRegistries = null,
        $propertyAccessorOrSecurityChecker = null,
        ?AuthorizationCheckerInterface $securityChecker = null
    ) {
        // NEXT_MAJOR: make the translator parameter required, move TranslatorInterface check to method signature
        // and remove this block

        if (null === $translator) {
            @trigger_error(
                'The $translator parameter will be required fields with the 4.0 release.',
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
                .' 3.x and will throw a \TypeError error in version 4.0. You MUST pass an instance of "%s" instead and pass'
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
                .' 3.x and will throw a \TypeError error in version 4.0. You must pass an instance of "%s" instead.',
                __METHOD__,
                PropertyAccessorInterface::class
            ), E_USER_DEPRECATED);

            $this->propertyAccessor = $pool->getPropertyAccessor();
            $this->securityChecker = $securityChecker;
        } else {
            $this->securityChecker = $securityChecker;
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
            new TwigFilter(
                'sonata_urlsafeid',
                [$this, 'getUrlSafeIdentifier']
            ),
            new TwigFilter(
                'sonata_xeditable_type',
                [$this, 'getXEditableType']
            ),
            new TwigFilter(
                'sonata_xeditable_choices',
                [$this, 'getXEditableChoices']
            ),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('canonicalize_locale_for_moment', [$this, 'getCanonicalizedLocaleForMoment'], ['needs_context' => true]),
            new TwigFunction('canonicalize_locale_for_select2', [$this, 'getCanonicalizedLocaleForSelect2'], ['needs_context' => true]),
            new TwigFunction('is_granted_affirmative', [$this, 'isGrantedAffirmative']),
        ];
    }

    public function getName()
    {
        return 'sonata_admin';
    }

    /**
     * render a list element from the FieldDescription.
     *
     * @param object|array $listElement
     * @param array        $params
     *
     * @return string
     */
    public function renderListElement(
        Environment $environment,
        $listElement,
        FieldDescriptionInterface $fieldDescription,
        $params = []
    ) {
        $template = $this->getTemplate(
            $fieldDescription,
            // NEXT_MAJOR: Remove this line and use commented line below instead
            $fieldDescription->getAdmin()->getTemplate('base_list_field'),
            //$this->getTemplateRegistry($fieldDescription->getAdmin()->getCode())->getTemplate('base_list_field'),
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
        return $this->render(
            $fieldDescription,
            new TemplateWrapper($environment, $template),
            $parameters,
            $environment
        );
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
                    'Accessing a non existing value is deprecated'
                    .' since sonata-project/admin-bundle 3.67 and will throw an exception in 4.0.',
                    E_USER_DEPRECATED
                );
            }
        }

        return $value;
    }

    /**
     * render a view element.
     *
     * @param object $object
     *
     * @return string
     */
    public function renderViewElement(
        Environment $environment,
        FieldDescriptionInterface $fieldDescription,
        $object
    ) {
        $template = $this->getTemplate(
            $fieldDescription,
            '@SonataAdmin/CRUD/base_show_field.html.twig',
            $environment
        );

        try {
            $value = $fieldDescription->getValue($object);
        } catch (NoValueException $e) {
            // NEXT_MAJOR: Remove the try catch in order to throw the NoValueException.
            @trigger_error(
                'Accessing a non existing value is deprecated'
                .' since sonata-project/admin-bundle 3.67 and will throw an exception in 4.0.',
                E_USER_DEPRECATED
            );

            $value = null;
        }

        return $this->render($fieldDescription, $template, [
            'field_description' => $fieldDescription,
            'object' => $object,
            'value' => $value,
            'admin' => $fieldDescription->getAdmin(),
        ], $environment);
    }

    /**
     * render a compared view element.
     *
     * @param mixed $baseObject
     * @param mixed $compareObject
     *
     * @return string
     */
    public function renderViewElementCompare(
        Environment $environment,
        FieldDescriptionInterface $fieldDescription,
        $baseObject,
        $compareObject
    ) {
        $template = $this->getTemplate(
            $fieldDescription,
            '@SonataAdmin/CRUD/base_show_field.html.twig',
            $environment
        );

        try {
            $baseValue = $fieldDescription->getValue($baseObject);
        } catch (NoValueException $e) {
            // NEXT_MAJOR: Remove the try catch in order to throw the NoValueException.
            @trigger_error(
                'Accessing a non existing value is deprecated'
                .' since sonata-project/admin-bundle 3.67 and will throw an exception in 4.0.',
                E_USER_DEPRECATED
            );

            $baseValue = null;
        }

        try {
            $compareValue = $fieldDescription->getValue($compareObject);
        } catch (NoValueException $e) {
            // NEXT_MAJOR: Remove the try catch in order to throw the NoValueException.
            @trigger_error(
                'Accessing a non existing value is deprecated'
                .' since sonata-project/admin-bundle 3.67 and will throw an exception in 4.0.',
                E_USER_DEPRECATED
            );

            $compareValue = null;
        }

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
            // For BC kept associated_tostring option behavior
            $method = $fieldDescription->getOption('associated_tostring');

            if ($method) {
                @trigger_error(
                    'Option "associated_tostring" is deprecated since version 2.3 and will be removed in 4.0. Use "associated_property" instead.',
                    E_USER_DEPRECATED
                );
            } else {
                $method = '__toString';
            }

            if (!method_exists($element, $method)) {
                throw new \RuntimeException(sprintf(
                    'You must define an `associated_property` option or create a `%s::__toString` method'
                    .' to the field option %s from service %s is ',
                    \get_class($element),
                    $fieldDescription->getName(),
                    $fieldDescription->getAdmin()->getCode()
                ));
            }

            return $element->{$method}();
        }

        if (\is_callable($propertyPath)) {
            return $propertyPath($element);
        }

        return $this->propertyAccessor->getValue($element, $propertyPath);
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
     * @param string[] $xEditableTypeMapping
     */
    public function setXEditableTypeMapping($xEditableTypeMapping)
    {
        $this->xEditableTypeMapping = $xEditableTypeMapping;
    }

    /**
     * @return string|bool
     */
    public function getXEditableType($type)
    {
        return $this->xEditableTypeMapping[$type] ?? false;
    }

    /**
     * Return xEditable choices based on the field description choices options & catalogue options.
     * With the following choice options:
     *     ['Status1' => 'Alias1', 'Status2' => 'Alias2']
     * The method will return:
     *     [['value' => 'Status1', 'text' => 'Alias1'], ['value' => 'Status2', 'text' => 'Alias2']].
     *
     * @return array
     */
    public function getXEditableChoices(FieldDescriptionInterface $fieldDescription)
    {
        $choices = $fieldDescription->getOption('choices', []);
        $catalogue = $fieldDescription->getOption('catalogue');
        $xEditableChoices = [];
        if (!empty($choices)) {
            reset($choices);
            $first = current($choices);
            // the choices are already in the right format
            if (\is_array($first) && \array_key_exists('value', $first) && \array_key_exists('text', $first)) {
                $xEditableChoices = $choices;
            } else {
                foreach ($choices as $value => $text) {
                    if ($catalogue) {
                        if (null !== $this->translator) {
                            $text = $this->translator->trans($text, [], $catalogue);
                        // NEXT_MAJOR: Remove this check
                        } elseif (method_exists($fieldDescription->getAdmin(), 'trans')) {
                            $text = $fieldDescription->getAdmin()->trans($text, [], $catalogue);
                        }
                    }

                    $xEditableChoices[] = [
                        'value' => $value,
                        'text' => $text,
                    ];
                }
            }
        }

        if (false === $fieldDescription->getOption('required', true)
            && false === $fieldDescription->getOption('multiple', false)
        ) {
            $xEditableChoices = array_merge([[
                'value' => '',
                'text' => '',
            ]], $xEditableChoices);
        }

        return $xEditableChoices;
    }

    /**
     * Returns a canonicalized locale for "moment" NPM library,
     * or `null` if the locale's language is "en", which doesn't require localization.
     *
     * @return string|null
     */
    final public function getCanonicalizedLocaleForMoment(array $context)
    {
        $locale = strtolower(str_replace('_', '-', $context['app']->getRequest()->getLocale()));

        // "en" language doesn't require localization.
        if (('en' === $lang = substr($locale, 0, 2)) && !\in_array($locale, ['en-au', 'en-ca', 'en-gb', 'en-ie', 'en-nz'], true)) {
            return null;
        }

        foreach (self::MOMENT_UNSUPPORTED_LOCALES as $language => $locales) {
            if ($language === $lang && !\in_array($locale, $locales, true)) {
                $locale = $language;
            }
        }

        return $locale;
    }

    /**
     * Returns a canonicalized locale for "select2" NPM library,
     * or `null` if the locale's language is "en", which doesn't require localization.
     *
     * @return string|null
     */
    final public function getCanonicalizedLocaleForSelect2(array $context)
    {
        $locale = str_replace('_', '-', $context['app']->getRequest()->getLocale());

        // "en" language doesn't require localization.
        if ('en' === $lang = substr($locale, 0, 2)) {
            return null;
        }

        switch ($locale) {
            case 'pt':
                $locale = 'pt-PT';
                break;
            case 'ug':
                $locale = 'ug-CN';
                break;
            case 'zh':
                $locale = 'zh-CN';
                break;
            default:
                if (!\in_array($locale, ['pt-BR', 'pt-PT', 'ug-CN', 'zh-CN', 'zh-TW'], true)) {
                    $locale = $lang;
                }
        }

        return $locale;
    }

    /**
     * @param string|array $role
     * @param object|null  $object
     * @param string|null  $field
     *
     * @return bool
     */
    public function isGrantedAffirmative($role, $object = null, $field = null)
    {
        if (null === $this->securityChecker) {
            return false;
        }

        if (null !== $field) {
            $object = new FieldVote($object, $field);
        }

        if (!\is_array($role)) {
            $role = [$role];
        }

        foreach ($role as $oneRole) {
            try {
                if ($this->securityChecker->isGranted($oneRole, $object)) {
                    return true;
                }
            } catch (AuthenticationCredentialsNotFoundException $e) {
                // empty on purpose
            }
        }

        return false;
    }

    /**
     * Get template.
     *
     * @param string $defaultTemplate
     *
     * @return TemplateWrapper
     */
    protected function getTemplate(
        FieldDescriptionInterface $fieldDescription,
        $defaultTemplate,
        Environment $environment
    ) {
        $templateName = $fieldDescription->getTemplate() ?: $defaultTemplate;

        try {
            $template = $environment->load($templateName);
        } catch (LoaderError $e) {
            @trigger_error(sprintf(
                'Relying on default template loading on field template loading exception is deprecated since 3.1'
                .' and will be removed in 4.0. A %s exception will be thrown instead',
                LoaderError::class
            ), E_USER_DEPRECATED);
            $template = $environment->load($defaultTemplate);

            if (null !== $this->logger) {
                $this->logger->warning(sprintf(
                    'An error occured trying to load the template "%s" for the field "%s",'
                    .' the default template "%s" was used instead.',
                    $templateName,
                    $fieldDescription->getFieldName(),
                    $defaultTemplate
                ), ['exception' => $e]);
            }
        }

        return $template;
    }

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
                $fieldDescription->getTemplate(),
                $template->getSourceContext()->getName(),
                $content,
                $fieldDescription->getFieldName()
            );
        }

        return $content;
    }

    /**
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    private function getTemplateRegistry(string $adminCode): TemplateRegistryInterface
    {
        $serviceId = $adminCode.'.template_registry';
        $templateRegistry = $this->templateRegistries->get($serviceId);

        if ($templateRegistry instanceof TemplateRegistryInterface) {
            return $templateRegistry;
        }

        throw new ServiceNotFoundException($serviceId);
    }

    /**
     * Extracts the object and requested value from the $listElement.
     *
     * @param object|array $listElement
     *
     * @throws \TypeError when $listElement is not an object or an array with an object on offset 0
     *
     * @return array An array containing object and value
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
            try {
                $value = $fieldDescription->getValue($object);
            } catch (NoValueException $e) {
                // NEXT_MAJOR: throw the NoValueException.
                @trigger_error(
                    'Accessing a non existing value is deprecated'
                    .' since sonata-project/admin-bundle 3.67 and will throw an exception in 4.0.',
                    E_USER_DEPRECATED
                );

                $value = null;
            }
        }

        return [$object, $value];
    }
}
