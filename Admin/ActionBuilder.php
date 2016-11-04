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

/**
 * @author Christian Gripp <mail@core23.de>
 */
final class ActionBuilder implements ActionBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getActionMenu(AdminInterface $admin, $action, AdminInterface $childAdmin = null)
    {
        $menu = $admin->getMenuFactory()->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');
        $menu->setExtra('translation_domain', $admin->getTranslationDomain());

        $object = $admin->getSubject();

        if (in_array($action, array('tree', 'show', 'edit', 'delete', 'list', 'batch'))
            && $admin->hasAccess('create')
            && $admin->hasRoute('create')
        ) {
            if ($admin->getSubClasses()) {
                $menu->addChild(
                    'divider',
                    array(
                        'label' => '',
                        'divider' => true,
                        'attributes' => array(
                            'class' => 'divider',
                        ),
                        'extras' => array(
                            'translation_domain' => false,
                        ),
                    )
                );

                foreach ($admin->getSubClasses() as $subClass) {
                    $menu->createItem(
                        'active',
                        array(
                            'uri' => $admin->generateUrl('create', array('subclass' => $subClass)),
                            'label' => $subClass,
                            'extras' => array(
                                'safe_label' => true,
                            ),
                            'attributes' => array(
                                'icon' => 'fa fa-plus-circle',
                            ),
                        )
                    );
                }

                $menu->addChild(
                    'divider',
                    array(
                        'label' => '',
                        'divider' => true,
                        'attributes' => array(
                            'class' => 'divider',
                        ),
                        'extras' => array(
                            'translation_domain' => false,
                        ),
                    )
                );
            } else {
                $menu->createItem(
                    'active',
                    array(
                        'uri' => $admin->generateUrl('create'),
                        'label' => 'link_action_create',
                        'extras' => array(
                            'safe_label' => true,
                        ),
                        'attributes' => array(
                            'icon' => 'fa fa-plus-circle',
                        ),
                    )
                );
            }
        }

        if (in_array($action, array('show', 'delete', 'acl', 'history'))
            && $admin->canAccessObject('edit', $object)
            && $admin->hasRoute('edit')
        ) {
            $menu->createItem(
                'edit',
                array(
                    'uri' => $admin->generateObjectUrl('edit', $object),
                    'label' => 'link_action_edit',
                    'extras' => array(
                        'safe_label' => true,
                    ),
                    'attributes' => array(
                        'icon' => 'fa fa-edit',
                    ),
                )
            );
        }

        if (in_array($action, array('show', 'edit', 'acl'))
            && $admin->canAccessObject('history', $object)
            && $admin->hasRoute('history')
        ) {
            $menu->createItem(
                'history',
                array(
                    'uri' => $admin->generateObjectUrl('history', $object),
                    'label' => 'link_action_history',
                    'extras' => array(
                        'safe_label' => true,
                    ),
                    'attributes' => array(
                        'icon' => 'fa fa-archive',
                    ),
                )
            );
        }

        if (in_array($action, array('edit', 'history'))
            && $admin->isAclEnabled()
            && $admin->canAccessObject('acl', $object)
            && $admin->hasRoute('acl')
        ) {
            $menu->createItem(
                'acl',
                array(
                    'uri' => $admin->generateObjectUrl('acl', $object),
                    'label' => 'link_action_acl',
                    'extras' => array(
                        'safe_label' => true,
                    ),
                    'attributes' => array(
                        'icon' => 'fa fa-users',
                    ),
                )
            );
        }

        if (in_array($action, array('edit', 'history', 'acl'))
            && $admin->canAccessObject('show', $object)
            && count($admin->getShow()) > 0
            && $admin->hasRoute('show')
        ) {
            $menu->createItem(
                'show',
                array(
                    'uri' => $admin->generateObjectUrl('show', $object),
                    'label' => 'link_action_show',
                    'extras' => array(
                        'safe_label' => true,
                    ),
                    'attributes' => array(
                        'icon' => 'fa fa-eye',
                    ),
                )
            );
        }

        if (in_array($action, array('show', 'edit', 'delete', 'acl', 'batch'))
            && $admin->hasAccess('list')
            && $admin->hasRoute('list')
        ) {
            $menu->createItem(
                'list',
                array(
                    'uri' => $admin->generateUrl('list'),
                    'label' => 'link_action_list',
                    'extras' => array(
                        'safe_label' => true,
                    ),
                    'attributes' => array(
                        'icon' => 'fa fa-list',
                    ),
                )
            );
        }

        $admin->configureActionMenu($menu, $action, $childAdmin);

        foreach ($admin->getExtensions() as $extension) {
            // NEXT_MAJOR: remove method check in next major release
            if (method_exists($extension, 'configureActionMenu')) {
                $extension->configureActionMenu($admin, $menu, $action, $childAdmin);
            }
        }

        return $menu;
    }
}
