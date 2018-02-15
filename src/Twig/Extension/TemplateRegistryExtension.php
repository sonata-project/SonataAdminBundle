<?php

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
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TemplateRegistryExtension extends AbstractExtension
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(Pool $pool, RequestStack $requestStack)
    {
        $this->pool = $pool;
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_admin_template', [$this, 'getTemplate']),
            new TwigFunction('get_admin_pool_template', [$this, 'getPoolTemplate']),
        ];
    }

    /**
     * @param string $name
     *
     * @return null|string
     */
    public function getTemplate($name)
    {
        return $this->getAdmin()->getTemplate($name);
    }

    /**
     * @param string $name
     *
     * @return null|string
     */
    public function getPoolTemplate($name)
    {
        return $this->pool->getTemplate($name);
    }

    /**
     * @return AdminInterface|false|null
     */
    private function getAdmin()
    {
        return $this->pool->getAdminByAdminCode(
            $this->requestStack->getCurrentRequest()->get('_sonata_admin')
        );
    }
}
