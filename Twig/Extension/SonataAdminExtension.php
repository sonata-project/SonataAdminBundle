<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Twig\Extension;

use Doctrine\Common\Util\ClassUtils;
use Knp\Menu\MenuFactory;
use Knp\Menu\ItemInterface;
use Knp\Menu\Twig\Helper;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Exception\NoValueException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class SonataAdminExtension
 *
 * @package Sonata\AdminBundle\Twig\Extension
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SonataAdminExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Helper
     */
    protected $knpHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Pool            $pool
     * @param RouterInterface $router
     * @param Helper          $knpHelper
     * @param LoggerInterface $logger
     */
    public function __construct(Pool $pool, RouterInterface $router, Helper $knpHelper, LoggerInterface $logger = null)
    {
        $this->pool      = $pool;
        $this->logger    = $logger;
        $this->router    = $router;
        $this->knpHelper = $knpHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            'render_list_element'           => new \Twig_Filter_Method($this,   'renderListElement', array('is_safe' => array('html'))),
            'render_view_element'           => new \Twig_Filter_Method($this,   'renderViewElement', array('is_safe' => array('html'))),
            'render_view_element_compare'   => new \Twig_Filter_Method($this,   'renderViewElementCompare', array('is_safe' => array('html'))),
            'render_relation_element'       => new \Twig_Filter_Method($this,   'renderRelationElement'),
            'sonata_urlsafeid'              => new \Twig_Filter_Method($this,   'getUrlsafeIdentifier'),
            'sonata_xeditable_type'         => new \Twig_Filter_Method($this,   'getXEditableType'),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            'sonata_knp_menu_build' => new \Twig_Function_Method($this, 'getKnpMenu'),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenParsers()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_admin';
    }

    /**
     * Get template
     *
     * @param FieldDescriptionInterface $fieldDescription
     * @param string                    $defaultTemplate
     *
     * @return \Twig_TemplateInterface
     */
    protected function getTemplate(FieldDescriptionInterface $fieldDescription, $defaultTemplate)
    {
        $templateName = $fieldDescription->getTemplate() ?: $defaultTemplate;

        try {
            $template = $this->environment->loadTemplate($templateName);
        } catch (\Twig_Error_Loader $e) {
            $template = $this->environment->loadTemplate($defaultTemplate);

            if (null !== $this->logger) {
                $this->logger->warning(sprintf('An error occured trying to load the template "%s" for the field "%s", the default template "%s" was used instead: "%s". ', $templateName, $fieldDescription->getFieldName(), $defaultTemplate, $e->getMessage()));
            }
        }

        return $template;
    }

    /**
     * render a list element from the FieldDescription
     *
     * @param mixed                     $object
     * @param FieldDescriptionInterface $fieldDescription
     * @param array                     $params
     *
     * @return string
     */
    public function renderListElement($object, FieldDescriptionInterface $fieldDescription, $params = array())
    {
        $template = $this->getTemplate($fieldDescription, $fieldDescription->getAdmin()->getTemplate('base_list_field'));

        return $this->output($fieldDescription, $template, array_merge($params, array(
            'admin'             => $fieldDescription->getAdmin(),
            'object'            => $object,
            'value'             => $this->getValueFromFieldDescription($object, $fieldDescription),
            'field_description' => $fieldDescription,
        )));
    }

    /**
     * @param FieldDescriptionInterface $fieldDescription
     * @param \Twig_TemplateInterface   $template
     * @param array                     $parameters
     *
     * @return string
     */
    public function output(FieldDescriptionInterface $fieldDescription, \Twig_TemplateInterface $template, array $parameters = array())
    {
        $content = $template->render($parameters);

        if ($this->environment->isDebug()) {
            return sprintf("\n<!-- START  \n  fieldName: %s\n  template: %s\n  compiled template: %s\n -->\n%s\n<!-- END - fieldName: %s -->",
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
     * exists => a temporary one is created
     *
     * @param object                    $object
     * @param FieldDescriptionInterface $fieldDescription
     * @param array                     $params
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function getValueFromFieldDescription($object, FieldDescriptionInterface $fieldDescription, array $params = array())
    {
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
     * render a view element
     *
     * @param FieldDescriptionInterface $fieldDescription
     * @param mixed                     $object
     *
     * @return string
     */
    public function renderViewElement(FieldDescriptionInterface $fieldDescription, $object)
    {
        $template = $this->getTemplate($fieldDescription, 'SonataAdminBundle:CRUD:base_show_field.html.twig');

        try {
            $value = $fieldDescription->getValue($object);
        } catch (NoValueException $e) {
            $value = null;
        }

        return $this->output($fieldDescription, $template, array(
            'field_description' => $fieldDescription,
            'object'            => $object,
            'value'             => $value,
            'admin'             => $fieldDescription->getAdmin(),
        ));
    }

    /**
     * render a compared view element
     *
     * @param FieldDescriptionInterface $fieldDescription
     * @param mixed                     $baseObject
     * @param mixed                     $compareObject
     *
     * @return string
     */
    public function renderViewElementCompare(FieldDescriptionInterface $fieldDescription, $baseObject, $compareObject)
    {
        $template = $this->getTemplate($fieldDescription, 'SonataAdminBundle:CRUD:base_show_field.html.twig');

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
            'admin'             => $fieldDescription->getAdmin(),
            'field_description' => $fieldDescription,
            'value'             => $baseValue,
        ));

        $compareValueOutput = $template->render(array(
            'field_description' => $fieldDescription,
            'admin'             => $fieldDescription->getAdmin(),
            'value'             => $compareValue,
        ));

        // Compare the rendered output of both objects by using the (possibly) overridden field block
        $isDiff = $baseValueOutput !== $compareValueOutput;

        return $this->output($fieldDescription, $template, array(
            'field_description' => $fieldDescription,
            'value'             => $baseValue,
            'value_compare'     => $compareValue,
            'is_diff'           => $isDiff,
            'admin'             => $fieldDescription->getAdmin(),
        ));
    }

    /**
     * @throws \RunTimeException
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
            $method = $fieldDescription->getOption('associated_tostring', '__toString');

            if (!method_exists($element, $method)) {
                throw new \RuntimeException(sprintf(
                    'You must define an `associated_property` option or create a `%s::__toString` method to the field option %s from service %s is ',
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

        return PropertyAccess::createPropertyAccessor()->getValue($element, $propertyPath);
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
            $admin = $this->pool->getAdminByClass(
                ClassUtils::getClass($model)
            );
        }

        return $admin->getUrlsafeIdentifier($model);
    }

    /**
     * @param $type
     *
     * @return string|bool
     */
    public function getXEditableType($type)
    {
        $mapping = array(
            'boolean'    => 'select',
            'text'       => 'text',
            'textarea'   => 'textarea',
            'email'      => 'email',
            'string'     => 'text',
            'smallint'   => 'text',
            'bigint'     => 'text',
            'integer'    => 'number',
            'decimal'    => 'number',
            'currency'   => 'number',
            'percent'    => 'number',
            'url'        => 'url',
        );

        return isset($mapping[$type]) ? $mapping[$type] : false;
    }

    /**
     * Get KnpMenu
     *
     * @param Request $request
     *
     * @return ItemInterface
     */
    public function getKnpMenu(Request $request = null)
    {
        $menuFactory = new MenuFactory();
        $menu = $menuFactory
            ->createItem('root')
            ->setExtra('request', $request)
        ;

        foreach ($this->pool->getAdminGroups() as $name => $group) {

            // Check if the menu group is built by a menu provider
            if (isset($group['provider'])) {
                $subMenu = $this->knpHelper->get($group['provider']);

                $menu->addChild($subMenu)
                    ->setAttributes(array(
                        'icon'            => $group['icon'],
                        'label_catalogue' => $group['label_catalogue']
                    ))
                    ->setExtra('roles', $group['roles']);

                continue;
            }

            // The menu group is built by config
            $menu
                ->addChild($name, array('label' => $group['label']))
                ->setAttributes(
                    array(
                        'icon'             => $group['icon'],
                        'label_catalogue'  => $group['label_catalogue'],
                    )
                )
                ->setExtra('roles', $group['roles'])
            ;

            foreach ($group['items'] as $item) {
                if (array_key_exists('admin', $item) && $item['admin'] != null) {
                    $admin             = $this->pool->getInstance($item['admin']);

                    // skip menu item if no `list` url is available or user doesn't have the LIST access rights
                    if (!$admin->hasRoute('list') || !$admin->isGranted('LIST') || !$admin->showIn(Admin::CONTEXT_MENU) {
                        continue;
                    }

                    $label             = $admin->getLabel();
                    $route             = $admin->generateUrl('list');
                    $translationDomain = $admin->getTranslationDomain();
                } else {
                    $label             = $item['label'];
                    $route             = $this->router->generate($item['route'], $item['route_params']);
                    $translationDomain = $group['label_catalogue'];
                    $admin             = null;
                }

                $menu[$name]
                    ->addChild($label, array('uri' => $route))
                    ->setExtra('translationdomain', $translationDomain)
                    ->setExtra('admin', $admin)
                ;
            }

            if (0 === count($menu[$name]->getChildren())) {
                $menu->removeChild($name);
            }
        }

        return $menu;
    }
}
