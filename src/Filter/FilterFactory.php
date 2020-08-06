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

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class FilterFactory implements FilterFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array<string, string>
     */
    private $types;

    /**
     * @param array<string, string> $types
     */
    public function __construct(ContainerInterface $container, array $types = [])
    {
        $this->container = $container;
        $this->types = $types;
    }

    public function create(string $name, string $type, array $options = []): FilterInterface
    {
        $id = isset($this->types[$type]) ? $this->types[$type] : false;

        if ($id) {
            $filter = $this->container->get($id);
        } elseif (class_exists($type)) {
            $filter = new $type();
        } else {
            throw new \RuntimeException(sprintf('No attached service to type named `%s`', $type));
        }

        if (!$filter instanceof FilterInterface) {
            throw new \RuntimeException(sprintf('The service `%s` must implement `FilterInterface`', $type));
        }

        $filter->initialize($name, $options);

        return $filter;
    }
}
