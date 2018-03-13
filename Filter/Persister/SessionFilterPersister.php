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
class SessionFilterPersister implements FilterPersisterInterface
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
     * {@inheritDoc}
     */
    public function get($admin)
    {
        return $this->session->get($this->buildStorageKey($admin), array());
    }

    /**
     * {@inheritDoc}
     */
    public function set($admin, $filters)
    {
        $this->session->set($this->buildStorageKey($admin), $filters);
    }

    /**
     * {@inheritDoc}
     */
    public function reset($admin)
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
    protected function buildStorageKey($admin)
    {
        return $admin.'.filter.parameters';
    }
}
