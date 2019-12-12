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

namespace Sonata\AdminBundle\Twig;

use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class GlobalVariables
{
    /**
     * @var Pool
     */
    protected $adminPool;

    /**
     * @var string|null
     */
    private $mosaicBackground;

    public function __construct(Pool $adminPool, ?string $mosaicBackground = null)
    {
        $this->mosaicBackground = $mosaicBackground;
        $this->adminPool = $adminPool;
    }

    /**
     * @return Pool
     */
    public function getAdminPool()
    {
        return $this->adminPool;
    }

    /**
     * @param string $code
     * @param string $action
     * @param array  $parameters
     * @param int    $absolute
     *
     * @return string
     */
    public function url($code, $action, $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        list($action, $code) = $this->getCodeAction($code, $action);

        return $this->getAdminPool()->getAdminByAdminCode($code)->generateUrl($action, $parameters, $absolute);
    }

    /**
     * @param string $code
     * @param string $action
     * @param object $object
     * @param array  $parameters
     * @param int    $absolute
     *
     * @return string
     */
    public function objectUrl($code, $action, $object, $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        list($action, $code) = $this->getCodeAction($code, $action);

        return $this->getAdminPool()->getAdminByAdminCode($code)->generateObjectUrl($action, $object, $parameters, $absolute);
    }

    public function getMosaicBackground(): ?string
    {
        return $this->mosaicBackground;
    }

    private function getCodeAction($code, $action): array
    {
        if ($pipe = strpos($code, '|')) {
            // convert code=sonata.page.admin.page|sonata.page.admin.snapshot, action=list
            // to => sonata.page.admin.page|sonata.page.admin.snapshot.list
            $action = $code.'.'.$action;
            $code = substr($code, 0, $pipe);
        }

        return [$action, $code];
    }
}
