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
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function get($adminCode): array
    {
        return $this->session->get($this->buildStorageKey($adminCode), []);
    }

    public function set($adminCode, array $filters): void
    {
        $this->session->set($this->buildStorageKey($adminCode), $filters);
    }

    public function reset($adminCode): void
    {
        $this->session->remove($this->buildStorageKey($adminCode));
    }

    /**
     * Build the session key, under which the filter should be stored for given admin code.
     */
    private function buildStorageKey(string $adminCode): string
    {
        return $adminCode.'.filter.parameters';
    }
}
