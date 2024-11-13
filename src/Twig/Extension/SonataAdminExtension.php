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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Twig\SonataAdminRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SonataAdminExtension extends AbstractExtension
{
    /**
     * NEXT_MAJOR: Remove this constructor.
     *
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private SonataAdminRuntime $sonataAdminRuntime,
    ) {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'sonata_urlsafeid',
                [SonataAdminRuntime::class, 'getUrlSafeIdentifier']
            ),
        ];
    }

    public function getName(): string
    {
        return 'sonata_admin';
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use SonataAdminRuntime::getUrlSafeIdentifier() instead
     *
     * Get the identifiers as a string that is safe to use in a url.
     *
     * @return string|null representation of the id that is safe to use in a url
     *
     * @phpstan-template T of object
     * @phpstan-param T $model
     * @phpstan-param AdminInterface<T>|null $admin
     */
    public function getUrlSafeIdentifier(object $model, ?AdminInterface $admin = null): ?string
    {
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            SonataAdminRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->sonataAdminRuntime->getUrlSafeIdentifier($model, $admin);
    }
}
