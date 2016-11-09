<?php

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
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SonataAdminExtension.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SonataAdminExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface|null
     */
    protected $translator;
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string[]
     */
    private $xEditableTypeMapping = array();

    /**
     * @param Pool                $pool
     * @param LoggerInterface     $logger
     * @param TranslatorInterface $translator
     */
    public function __construct(Pool $pool, LoggerInterface $logger = null, TranslatorInterface $translator)
    {
        $this->pool = $pool;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'render_list_element',
                array($this, 'renderListElement'),
                array(
                    'is_safe' => array('html'),
                    'needs_environment' => true,
                )
            ),
            new \Twig_SimpleFilter(
                'render_view_element',
                array($this, 'renderViewElement'),
                array(
                    'is_safe' => array('html'),
                    'needs_environment' => true,
                )
            ),
            new \Twig_SimpleFilter(
                'render_view_element_compare',
                array($this, 'renderViewElementCompare'),
                array(
                    'is_safe' => array('html'),
                    'needs_environment' => true,
                )
            ),
            new \Twig_SimpleFilter(
                'render_relation_element',
                array($this, 'renderRelationElement')
            ),
            new \Twig_SimpleFilter(
                'sonata_urlsafeid',
                array($this, 'getUrlsafeIdentifier')
            ),
            new \Twig_SimpleFilter(
                'sonata_xeditable_type',
                array($this, 'getXEditableType')
            ),
            new \Twig_SimpleFilter(
                'sonata_xeditable_choices',
                array($this, 'getXEditableChoices')
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_admin';
    }

    /**
     * render a list element from the FieldDescription.
     *
     * @param mixed                     $object
     * @param FieldDescriptionInterface $fieldDescription
     * @param array                     $params
     *
     * @return string
     */
    public function renderListElement(
        \Twig_Environment $environment,
        $object,
        FieldDescriptionInterface $fieldDescription,
        $params = array()
    ) {
        $template = $this->getTemplate(
            $fieldDescription,
            $fieldDescription->getAdmin()->getTemplate('base_list_field'),
            $environment
        );

        return $this->output($fieldDescription, $template, array_merge($params, array(
            'admin' => $fieldDescription->getAdmin(),
            'object' => $object,
            'value' => $this->getValueFromFieldDescription($object, $fieldDescription),
            'field_description' => $fieldDescription,
        )), $environment);
    }

    /**
     * render a view element.
     *
     * @param FieldDescriptionInterface $fieldDescription
     * @param mixed                     $object
     *
     * @return string
     */
    public function renderViewElement(
        \Twig_Environment $environment,
        FieldDescriptionInterface $fieldDescription,
        $object
    ) {
        $template = $this->getTemplate(
            $fieldDescription,
            'SonataAdminBundle:CRUD:base_show_field.html.twig',
            $environment
        );

        try {
            $value = $fieldDescription->getValue($object);
        } catch (NoValueException $e) {
            $value = null;
        }

        return $this->output($fieldDescription, $template, array(
            'field_description' => $fieldDescription,
            'object' => $object,
            'value' => $value,
            'admin' => $fieldDescription->getAdmin(),
        ), $environment);
    }

    /**
     * render a compared view element.
     *
     * @param FieldDescriptionInterface $fieldDescription
     * @param mixed                     $baseObject
     * @param mixed                     $compareObject
     *
     * @return string
     */
    public function renderViewElementCompare(
        \Twig_Environment $environment,
        FieldDescriptionInterface $fieldDescription,
        $baseObject,
        $compareObject
    ) {
        $template = $this->getTemplate(
            $fieldDescription,
            'SonataAdminBundle:CRUD:base_show_field.html.twig',
            $environment
        );

        try {
            $baseValue = $fieldDescription->getValue($baseObject);
        } catch (NoValueException $e) {
            $baseValue = null;
        }

        try {
            $compareValue = $fieldDescription->getValue($compareObject);
        } catch (NoValueException $e) {
            $compareValue = null;
        }

        $baseValueOutput = $template->render(array(
            'admin' => $fieldDescription->getAdmin(),
            'field_description' => $fieldDescription,
            'value' => $baseValue,
        ));

        $compareValueOutput = $template->render(array(
            'field_description' => $fieldDescription,
            'admin' => $fieldDescription->getAdmin(),
            'value' => $compareValue,
        ));

        // Compare the rendered output of both objects by using the (possibly) overridden field block
        $isDiff = $baseValueOutput !== $compareValueOutput;

        return $this->output($fieldDescription, $template, array(
            'field_description' => $fieldDescription,
            'value' => $baseValue,
            'value_compare' => $compareValue,
            'is_diff' => $isDiff,
            'admin' => $fieldDescription->getAdmin(),
        ), $environment);
    }

    /**
     * @throws \RuntimeException
     *
     * @param mixed                     $element
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return mixed
     */
    public function renderRelationElement($element, FieldDescriptionInterface $fieldDescription)
    {
        if (!is_object($element)) {
            return $element;
        }

        $propertyPath = $fieldDescription->getOption('associated_property');

        if (null === $propertyPath) {
            // For BC kept associated_tostring option behavior
            $method = $fieldDescription->getOption('associated_tostring');

            if ($method) {
                @trigger_error(
                    'Option "associated_tostring" is deprecated since version 2.3 and will be removed in 4.0. '
                    .'Use "associated_property" instead.',
                    E_USER_DEPRECATED
                );
            } else {
                $method = '__toString';
            }

            if (!method_exists($element, $method)) {
                throw new \RuntimeException(sprintf(
                    'You must define an `associated_property` option or '.
                    'create a `%s::__toString` method to the field option %s from service %s is ',
                    get_class($element),
                    $fieldDescription->getName(),
                    $fieldDescription->getAdmin()->getCode()
                ));
            }

            return call_user_func(array($element, $method));
        }

        if (is_callable($propertyPath)) {
            return $propertyPath($element);
        }

        return $this->pool->getPropertyAccessor()->getValue($element, $propertyPath);
    }

    /**
     * Get the identifiers as a string that is save to use in an url.
     *
     * @param object         $model
     * @param AdminInterface $admin
     *
     * @return string string representation of the id that is save to use in an url
     */
    public function getUrlsafeIdentifier($model, AdminInterface $admin = null)
    {
        if (is_null($admin)) {
            $admin = $this->pool->getAdminByClass(ClassUtils::getClass($model));
        }

        return $admin->getUrlsafeIdentifier($model);
    }

    /**
     * @param string[] $xEditableTypeMapping
     */
    public function setXEditableTypeMapping($xEditableTypeMapping)
    {
        $this->xEditableTypeMapping = $xEditableTypeMapping;
    }

    /**
     * @param $type
     *
     * @return string|bool
     */
    public function getXEditableType($type)
    {
        return isset($this->xEditableTypeMapping[$type]) ? $this->xEditableTypeMapping[$type] : false;
    }

    /**
     * Return xEditable choices based on the field description choices options & catalogue options.
     * With the following choice options:
     *     ['Status1' => 'Alias1', 'Status2' => 'Alias2']
     * The method will return:
     *     [['value' => 'Status1', 'text' => 'Alias1'], ['value' => 'Status2', 'text' => 'Alias2']].
     *
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return array
     */
    public function getXEditableChoices(FieldDescriptionInterface $fieldDescription)
    {
        $choices = $fieldDescription->getOption('choices', array());
        $catalogue = $fieldDescription->getOption('catalogue');
        $xEditableChoices = array();
        if (!empty($choices)) {
            reset($choices);
            $first = current($choices);
            // the choices are already in the right format
            if (is_array($first) && array_key_exists('value', $first) && array_key_exists('text', $first)) {
                $xEditableChoices = $choices;
            } else {
                foreach ($choices as $value => $text) {
                    if ($catalogue) {
                        $this->translator->trans($text, array(), $catalogue);
                    }

                    $xEditableChoices[] = array(
                        'value' => $value,
                        'text' => $text,
                    );
                }
            }
        }

        return $xEditableChoices;
    }

    /**
     * @param FieldDescriptionInterface $fieldDescription
     * @param \Twig_Template            $template
     * @param array                     $parameters
     *
     * @return string
     */
    private function output(
        FieldDescriptionInterface $fieldDescription,
        \Twig_Template $template,
        array $parameters,
        \Twig_Environment $environment
    ) {
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
                $template->getTemplateName(),
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
     * @param object                    $object
     * @param FieldDescriptionInterface $fieldDescription
     * @param array                     $params
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    private function getValueFromFieldDescription(
        $object,
        FieldDescriptionInterface $fieldDescription,
        array $params = array()
    ) {
        if (isset($params['loop']) && $object instanceof \ArrayAccess) {
            throw new \RuntimeException('remove the loop requirement');
        }

        $value = null;
        try {
            $value = $fieldDescription->getValue($object);
        } catch (NoValueException $e) {
            if ($fieldDescription->getAssociationAdmin()) {
                $value = $fieldDescription->getAssociationAdmin()->getNewInstance();
            }
        }

        return $value;
    }

    /**
     * Get template.
     *
     * @param FieldDescriptionInterface $fieldDescription
     * @param string                    $defaultTemplate
     *
     * @return \Twig_Template
     */
    private function getTemplate(
        FieldDescriptionInterface $fieldDescription,
        $defaultTemplate,
        \Twig_Environment $environment
    ) {
        $templateName = $fieldDescription->getTemplate() ?: $defaultTemplate;

        return $environment->loadTemplate($templateName);
    }
}
