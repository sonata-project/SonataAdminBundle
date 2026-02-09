<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\User\Twig;

use SensioLabs\AdminBundle\User\Security\RolesBuilder\MatrixRolesBuilderInterface;
use Symfony\Component\Form\FormView;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

final class RolesMatrixRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly MatrixRolesBuilderInterface $matrixRolesBuilder,
    ) {
    }

    public function renderMatrix(FormView $form): string
    {
        $adminRoles = $this->matrixRolesBuilder->getRoles();
        $permissionLabels = $this->matrixRolesBuilder->getPermissionLabels();

        // Group roles by admin code
        $adminGroups = [];
        foreach ($adminRoles as $role => $attributes) {
            if (!isset($attributes['admin_code'])) {
                continue;
            }

            $adminCode = $attributes['admin_code'];
            $adminGroups[$adminCode] ??= [
                'label' => $attributes['admin_label'] ?? $adminCode,
                'translation_domain' => $attributes['admin_translation_domain'] ?? 'messages',
                'roles' => [],
            ];
            $adminGroups[$adminCode]['roles'][$role] = $attributes;
        }

        return $this->twig->render('@SensioLabsAdmin/User/Form/roles_matrix.html.twig', [
            'form' => $form,
            'admin_groups' => $adminGroups,
            'permission_labels' => $permissionLabels,
        ]);
    }

    public function renderRolesList(FormView $form): string
    {
        $securityRoles = [];

        foreach ($this->matrixRolesBuilder->getExpandedRoles() as $role => $attributes) {
            if (!isset($attributes['admin_code'])) {
                $securityRoles[$role] = $attributes;
            }
        }

        return $this->twig->render('@SensioLabsAdmin/User/Form/roles_matrix_list.html.twig', [
            'form' => $form,
            'security_roles' => $securityRoles,
        ]);
    }
}
