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
     * {@inheritdoc}
     */
    public function getBreadcrumbs(AdminInterface $admin, $action)
    {
        $breadcrumbs = array();
        if ($admin->isChild()) {
            return $admin->getParent()->getBreadcrumbs($action);
        }

        $menu = $admin->buildBreadcrumbs($action);

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
    public function buildBreadcrumbs(AdminInterface $admin, $action, ItemInterface $menu = null)
    {
        if (!$menu) {
            $menu = $admin->getMenuFactory()->createItem('root');

            $menu = $menu->addChild(
                $admin->trans(
                    $admin->getLabelTranslatorStrategy()->getLabel(
                        'dashboard',
                        'breadcrumb',
                        'link'
                    ),
                    array(),
                    'SonataAdminBundle'
                ),
                array('uri' => $admin->getRouteGenerator()->generate(
                    'sonata_admin_dashboard'
                ))
            );
        }

        $menu = $menu->addChild(
            $admin->trans(
                $admin->getLabelTranslatorStrategy()->getLabel(sprintf(
                    '%s_list',
                    $admin->getClassnameLabel()
                ), 'breadcrumb', 'link')
            ),
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
                    'uri' => $admin->hasRoute('edit') && $admin->isGranted('EDIT') ?
                    $admin->generateUrl('edit', array('id' => $id)) :
                    null,
                )
            );

            return $childAdmin->buildBreadcrumbs($action, $menu);
        }

        if ('list' === $action && $admin->isChild()) {
            $menu->setUri(false);
        } elseif ('create' !== $action && $admin->hasSubject()) {
            $menu = $menu->addChild($admin->toString($admin->getSubject()));
        } else {
            $menu = $menu->addChild(
                $admin->trans(
                    $admin->getLabelTranslatorStrategy()->getLabel(
                        sprintf('%s_%s', $admin->getClassnameLabel(), $action),
                        'breadcrumb',
                        'link'
                    )
                )
            );
        }

        return $menu;
    }
}
