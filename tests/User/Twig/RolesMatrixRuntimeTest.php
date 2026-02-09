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

namespace SensioLabs\AdminBundle\Tests\User\Twig;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\User\Security\RolesBuilder\MatrixRolesBuilderInterface;
use SensioLabs\AdminBundle\User\Twig\RolesMatrixRuntime;
use Symfony\Component\Form\FormView;
use Twig\Environment;

final class RolesMatrixRuntimeTest extends TestCase
{
    public function testRenderMatrix(): void
    {
        $matrixBuilder = $this->createMock(MatrixRolesBuilderInterface::class);
        $matrixBuilder->method('getRoles')->willReturn([
            'ROLE_ADMIN_USER_LIST' => [
                'role' => 'ROLE_ADMIN_USER_LIST',
                'label' => 'LIST',
                'admin_code' => 'app.admin.user',
                'admin_label' => 'User',
                'admin_translation_domain' => 'messages',
                'is_granted' => true,
            ],
        ]);
        $matrixBuilder->method('getPermissionLabels')->willReturn(['LIST' => 'LIST']);

        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())
            ->method('render')
            ->with(
                '@SensioLabsAdmin/User/Form/roles_matrix.html.twig',
                self::callback(static function (array $context): bool {
                    return isset($context['admin_groups']['app.admin.user'])
                        && isset($context['permission_labels'])
                        && isset($context['form']);
                }),
            )
            ->willReturn('<table>matrix</table>');

        $runtime = new RolesMatrixRuntime($twig, $matrixBuilder);
        $result = $runtime->renderMatrix($this->createMock(FormView::class));

        self::assertSame('<table>matrix</table>', $result);
    }

    public function testRenderRolesList(): void
    {
        $matrixBuilder = $this->createMock(MatrixRolesBuilderInterface::class);
        $matrixBuilder->method('getExpandedRoles')->willReturn([
            'ROLE_ADMIN' => [
                'role' => 'ROLE_ADMIN',
                'is_granted' => true,
            ],
        ]);

        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())
            ->method('render')
            ->willReturn('<div>roles list</div>');

        $runtime = new RolesMatrixRuntime($twig, $matrixBuilder);
        $result = $runtime->renderRolesList($this->createMock(FormView::class));

        self::assertSame('<div>roles list</div>', $result);
    }
}
