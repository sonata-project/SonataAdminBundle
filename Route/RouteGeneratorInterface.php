<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Sonata\AdminBundle\Route;

use Sonata\AdminBundle\Admin\AdminInterface;

interface RouteGeneratorInterface
{
    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param string                                   $name
     * @param array                                    $parameters
     * @param bool                                     $absolute
     *
     * @return string
     */
    public function generateUrl(AdminInterface $admin, $name, array $parameters = array(), $absolute = false);

    /**
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     *
     * @return string
     */
    public function generate($name, array $parameters = array(), $absolute = false);
}
