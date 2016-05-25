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

/**
 * Stateless breadcrumbs builder (each method needs an Admin object).
 *
 * @author Grégoire Paris <postmaster@greg0ire.fr>
 */
final class BreadcrumbsBuilder implements BreadcrumbsBuilderInterface
{
    /**
     * @var AdminInterface might be the current admin or one of its ancestors.
     */
    private $admin;

    /**
     * @param AdminInterface will be used to get various services. To be replaced by several dependencies
     */
    public function __construct(AdminInterface $admin)
    {
        $this->admin = $admin;
    }

    /**
     * {@inheritdoc}
     */
    public function getBreadcrumbs($action)
    {
        $breadcrumbs = array();
        if ($this->admin->isChild()) {
            return $this->admin->getParent()->getBreadcrumbs($action);
        }

        $menu = $this->admin->buildBreadcrumbs($action);

        do {
            $breadcrumbs[] = $menu;
        } while ($menu = $menu->getParent());

        $breadcrumbs = array_reverse($breadcrumbs);
        array_shift($breadcrumbs);

        return $breadcrumbs;
    }

    /**
     * {@inheritdoc}
     */
    public function buildBreadcrumbs($action, ItemInterface $menu = null)
    {
        if (!$menu) {
            $menu = $this->admin->getMenuFactory()->createItem('root');

            $menu = $this->createMenuItem(
                $menu,
                'dashboard',
                'SonataAdminBundle',
                array('uri' => $this->admin->getRouteGenerator()->generate(
                    'sonata_admin_dashboard'
                ))
            );
        }

        $menu = $this->createMenuItem(
            $menu,
            sprintf('%s_list', $this->admin->getClassnameLabel()),
            null,
            array(
                'uri' => $this->admin->hasRoute('list') && $this->admin->isGranted('LIST') ?
                $this->admin->generateUrl('list') :
                null,
            )
        );

        $childAdmin = $this->admin->getCurrentChildAdmin();

        if ($childAdmin) {
            $menu = $menu->addChild(
                $this->admin->toString($this->admin->getSubject()),
                array(
                    'uri' => $this->admin->hasRoute('edit') && $this->admin->isGranted('EDIT') ?
                    $this->admin->generateUrl('edit', array(
                        'id' => $this->admin->getRequest()->get($this->admin->getIdParameter()),
                    )) :
                    null,
                )
            );

            return $childAdmin->buildBreadcrumbs($action, $menu);
        }

        if ('list' === $action && $this->admin->isChild()) {
            $menu->setUri(false);
        } elseif ('create' !== $action && $this->admin->hasSubject()) {
            $menu = $menu->addChild($this->admin->toString($this->admin->getSubject()));
        } else {
            $menu = $this->createMenuItem(
                $menu,
                sprintf('%s_%s', $this->admin->getClassnameLabel(), $action)
            );
        }

        return $menu;
    }

    /**
     * Creates a new menu item from a simple name. The name is normalized and
     * translated with the specified translation domain.
     *
     * @param ItemInterface $menu              will be modified and returned
     * @param string        $name              the source of the final label
     * @param string        $translationDomain for label translation
     * @param array         $options           menu item options
     *
     * @return ItemInterface
     */
    private function createMenuItem(
        ItemInterface $menu,
        $name,
        $translationDomain = null,
        $options = array()
    ) {
        return $menu->addChild(
            $this->admin->trans(
                $this->admin->getLabelTranslatorStrategy()->getLabel(
                    $name,
                    'breadcrumb',
                    'link'
                ),
                array(),
                $translationDomain
            ),
            $options
        );
    }
}
