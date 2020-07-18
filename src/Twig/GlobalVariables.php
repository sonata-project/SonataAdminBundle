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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class GlobalVariables
{
    /**
     * @var Pool
     */
    private $adminPool;

    /**
     * @var string|null
     */
    private $mosaicBackground;

    public function __construct(Pool $adminPool, ?string $mosaicBackground = null)
    {
        $this->adminPool = $adminPool;
        $this->mosaicBackground = $mosaicBackground;
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
