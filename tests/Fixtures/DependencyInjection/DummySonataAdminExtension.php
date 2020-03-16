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

namespace Sonata\AdminBundle\Tests\Fixtures\DependencyInjection;

use Sonata\AdminBundle\DependencyInjection\AbstractSonataAdminExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DummySonataAdminExtension extends AbstractSonataAdminExtension
{
    /**
     * @var array
     */
    public $configs;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->configs = $this->fixTemplatesConfiguration($configs, $container);
    }
}
