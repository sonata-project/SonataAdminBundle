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
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TemplateRegistryExtension extends AbstractExtension
{
    /**
     * @var Pool
     */
    private $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_admin_template', [$this, 'getAdminTemplate']),
            new TwigFunction('get_admin_pool_template', [$this, 'getPoolTemplate']),
        ];
    }

    /**
     * @param string $name
     * @param string $adminCode
     *
     * @return null|string
     */
    public function getAdminTemplate($name, $adminCode)
    {
        return $this->getAdmin($adminCode)->getTemplate($name);
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
     * @param string $adminCode
     *
     * @return AdminInterface|false|null
     */
    private function getAdmin($adminCode)
    {
        return $this->pool->getAdminByAdminCode($adminCode);
    }
}
