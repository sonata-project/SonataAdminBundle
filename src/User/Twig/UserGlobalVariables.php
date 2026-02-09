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

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class UserGlobalVariables
{
    private ?string $userAdminServiceId = null;

    public function __construct(
        private readonly Pool $pool,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly string $defaultAvatar,
    ) {
    }

    public function setUserAdminServiceId(?string $userAdminServiceId): void
    {
        $this->userAdminServiceId = $userAdminServiceId;
    }

    public function getImpersonating(): bool
    {
        $token = $this->tokenStorage->getToken();

        return $token instanceof SwitchUserToken;
    }

    public function getDefaultAvatar(): string
    {
        return $this->defaultAvatar;
    }

    /**
     * @return AdminInterface<object>|null
     */
    public function getUserAdmin(): ?AdminInterface
    {
        if (null === $this->userAdminServiceId) {
            return null;
        }

        try {
            return $this->pool->getInstance($this->userAdminServiceId);
        } catch (\Exception) {
            return null;
        }
    }
}
