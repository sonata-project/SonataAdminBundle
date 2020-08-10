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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class GlobalVariables
{
    /**
     * @var ContainerInterface
     *
     * @deprecated since sonata-project/admin-bundle 3.5, will be removed in 4.0.
     * NEXT_MAJOR : remove this property
     */
    protected $container;

    /**
     * @var Pool
     */
    protected $adminPool;

    /**
     * @var string|null
     */
    private $mosaicBackground;

    /**
     * @param ContainerInterface|Pool $adminPool
     */
    public function __construct($adminPool, ?string $mosaicBackground = null)
    {
        $this->mosaicBackground = $mosaicBackground;

        // NEXT_MAJOR : remove this block and set adminPool from parameter.
        if ($adminPool instanceof ContainerInterface) {
            @trigger_error(sprintf(
                'Using an instance of %s is deprecated since version 3.5 and will be removed in 4.0. Use %s instead.',
                ContainerInterface::class,
                Pool::class
            ), E_USER_DEPRECATED);

            $adminPool = $adminPool->get('sonata.admin.pool');
        }

        if ($adminPool instanceof Pool) {
            $this->adminPool = $adminPool;

            return;
        }

        throw new \InvalidArgumentException(sprintf('$adminPool should be an instance of %s', Pool::class));
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
     * @param int    $referenceType
     *
     * @return string
     */
    public function url($code, $action, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        list($action, $code) = $this->getCodeAction($code, $action);

        return $this->getAdminPool()->getAdminByAdminCode($code)->generateUrl($action, $parameters, $referenceType);
    }

    /**
     * @param string $code
     * @param string $action
     * @param object $object
     * @param array  $parameters
     * @param int    $referenceType
     *
     * @return string
     */
    public function objectUrl($code, $action, $object, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        list($action, $code) = $this->getCodeAction($code, $action);

        return $this->getAdminPool()->getAdminByAdminCode($code)->generateObjectUrl($action, $object, $parameters, $referenceType);
    }

    public function getMosaicBackground(): ?string
    {
        return $this->mosaicBackground;
    }

    private function getCodeAction(string $code, string $action): array
    {
        if ($pipe = strpos($code, '|')) {
            // convert code=sonata.page.admin.page|sonata.page.admin.snapshot, action=list
            // to => sonata.page.admin.page|sonata.page.admin.snapshot.list
            $action = sprintf('%s.%s', $code, $action);
            $code = substr($code, 0, $pipe);
        }

        return [$action, $code];
    }
}
