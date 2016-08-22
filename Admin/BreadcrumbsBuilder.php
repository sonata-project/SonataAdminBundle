<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Knp\Menu\ItemInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Stateless breadcrumbs builder (each method needs an Admin object).
 *
 * @author Grégoire Paris <postmaster@greg0ire.fr>
 */
final class BreadcrumbsBuilder implements BreadcrumbsBuilderInterface
{
    /**
     * @var string[]
     */
    protected $config = array();

    /**
     * @param string[] $config
     */
    public function __construct(array $config = array())
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->config = $resolver->resolve($config);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'child_admin_route' => 'edit',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBreadcrumbs(AdminInterface $admin, $action)
    {
        $breadcrumbs = array();
        if ($admin->isChild()) {
            return $this->getBreadcrumbs($admin->getParent(), $action);
        }

        $menu = $this->buildBreadcrumbs($admin, $action);

        do {
            $breadcrumbs[] = $menu;
        } while ($menu = $menu->getParent());

        $breadcrumbs = array_reverse($breadcrumbs);
        array_shift($breadcrumbs);

        return $breadcrumbs;
    }

    /**
     * {@inheritdoc}
     * NEXT_MAJOR : make this method private.
     */
    public function buildBreadcrumbs(AdminInterface $admin, $action, ItemInterface $menu = null)
    {
        if (!$menu) {
            $menu = $admin->getMenuFactory()->createItem('root');

            $menu = $this->createMenuItem(
                $admin,
                $menu,
                'dashboard',
                'SonataAdminBundle',
                array('uri' => $admin->getRouteGenerator()->generate(
                    'sonata_admin_dashboard'
                ))
            );
        }

        $menu = $this->createMenuItem(
            $admin,
            $menu,
            sprintf('%s_list', $admin->getClassnameLabel()),
            $admin->getTranslationDomain(),
            array(
                'uri' => $admin->hasRoute('list') && $admin->isGranted('LIST') ?
                $admin->generateUrl('list') :
                null,
            )
        );

        $childAdmin = $admin->getCurrentChildAdmin();

        if ($childAdmin) {
            $id = $admin->getRequest()->get($admin->getIdParameter());

            $menu = $menu->addChild(
                $admin->toString($admin->getSubject()),
                array(
                    'uri' => $admin->hasRoute($this->config['child_admin_route']) && $admin->hasAccess($this->config['child_admin_route'], $admin->getSubject()) ?
                    $admin->generateUrl($this->config['child_admin_route'], array('id' => $id)) :
                    null,
                    'extras' => array(
                        'translation_domain' => false,
                    ),
                )
            );

            $menu->setExtra('safe_label', false);

            return $this->buildBreadcrumbs($childAdmin, $action, $menu);
        }

        if ('list' === $action && $admin->isChild()) {
            $menu->setUri(false);
        } elseif ('create' !== $action && $admin->hasSubject()) {
            $menu = $menu->addChild($admin->toString($admin->getSubject()), array(
                'extras' => array(
                    'translation_domain' => false,
                ),
            ));
        } else {
            $menu = $this->createMenuItem(
                $admin,
                $menu,
                sprintf('%s_%s', $admin->getClassnameLabel(), $action),
                $admin->getTranslationDomain()
            );
        }

        return $menu;
    }

    /**
     * Creates a new menu item from a simple name. The name is normalized and
     * translated with the specified translation domain.
     *
     * @param AdminInterface $admin             used for translation
     * @param ItemInterface  $menu              will be modified and returned
     * @param string         $name              the source of the final label
     * @param string         $translationDomain for label translation
     * @param array          $options           menu item options
     *
     * @return ItemInterface
     */
    private function createMenuItem(
        AdminInterface $admin,
        ItemInterface $menu,
        $name,
        $translationDomain = null,
        $options = array()
    ) {
        $options = array_merge(array(
            'extras' => array(
                'translation_domain' => $translationDomain,
            ),
        ), $options);

        return $menu->addChild(
            $admin->getLabelTranslatorStrategy()->getLabel(
                $name,
                'breadcrumb',
                'link'
            ),
            $options
        );
    }
}
