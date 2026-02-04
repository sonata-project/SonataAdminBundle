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

namespace SensioLabs\AdminBundle\Filter\Persister;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This filter persister is storing filters in session.
 * This is the default behavior.
 *
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
final class SessionFilterPersister implements FilterPersisterInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function get(string $adminCode): array
    {
        return $this->requestStack->getSession()->get($this->buildStorageKey($adminCode), []);
    }

    public function set(string $adminCode, array $filters): void
    {
        $this->requestStack->getSession()->set($this->buildStorageKey($adminCode), $filters);
    }

    public function reset(string $adminCode): void
    {
        $this->requestStack->getSession()->remove($this->buildStorageKey($adminCode));
    }

    /**
     * Build the session key, under which the filter should be stored for given admin code.
     */
    private function buildStorageKey(string $adminCode): string
    {
        return \sprintf('%s.filter.parameters', $adminCode);
    }
}
