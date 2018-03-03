<?php

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

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $admin): array
    {
        return $this->session->get($this->buildStorageKey($admin), []);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $admin, array $filters): void
    {
        $this->session->set($this->buildStorageKey($admin), $filters);
    }

    /**
     * {@inheritdoc}
     */
    public function reset(string $admin): void
    {
        $this->session->remove($this->buildStorageKey($admin));
    }

    /**
     * Build the session key, under which the filter should be stored for given admin code.
     *
     * @param string $admin The admin code
     *
     * @return string The storage key
     */
    private function buildStorageKey(string $admin): string
    {
        return $admin.'.filter.parameters';
    }
}
