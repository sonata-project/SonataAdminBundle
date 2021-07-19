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

namespace Sonata\AdminBundle\Filter\Persister;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * This filter persister is storing filters in session.
 * This is the default behavior.
 *
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
final class SessionFilterPersister implements FilterPersisterInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function get(string $adminCode): array
    {
        // TODO: Use $this->requestStack->getSession() when dropping support of Symfony < 5.3
        return $this->getSession()->get($this->buildStorageKey($adminCode), []);
    }

    public function set(string $adminCode, array $filters): void
    {
        // TODO: Use $this->requestStack->getSession() when dropping support of Symfony < 5.3
        $this->getSession()->set($this->buildStorageKey($adminCode), $filters);
    }

    public function reset(string $adminCode): void
    {
        // TODO: Use $this->requestStack->getSession() when dropping support of Symfony < 5.3
        $this->getSession()->remove($this->buildStorageKey($adminCode));
    }

    /**
     * Build the session key, under which the filter should be stored for given admin code.
     */
    private function buildStorageKey(string $adminCode): string
    {
        return sprintf('%s.filter.parameters', $adminCode);
    }

    /**
     * TODO: Remove it when dropping support of Symfony < 5.3.
     */
    private function getSession(): SessionInterface
    {
        // @phpstan-ignore-next-line
        if (method_exists($this->requestStack, 'getSession')) {
            return $this->requestStack->getSession();
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new \LogicException('There is currently no session available.');
        }

        return $request->getSession();
    }
}
