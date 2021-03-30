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

namespace Sonata\AdminBundle\Filter;

use Psr\Container\ContainerInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class FilterFactory implements FilterFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(string $name, string $type, array $options = []): FilterInterface
    {
        if (!$this->container->has($type)) {
            throw new \RuntimeException(sprintf('No attached service to type named `%s`', $type));
        }

        $filter = $this->container->get($type);

        if (!$filter instanceof FilterInterface) {
            throw new \RuntimeException(sprintf('The service `%s` must implement `FilterInterface`', $type));
        }

        $filter->initialize($name, $options);

        return $filter;
    }
}
