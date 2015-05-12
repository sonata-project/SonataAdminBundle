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
namespace Sonata\AdminBundle\Serializer\Hateoas;

use Hateoas\Configuration\Provider\Resolver\RelationProviderResolverInterface;
use Sonata\AdminBundle\Admin\Pool;
use Hateoas\Configuration\RelationProvider;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Hateoas\Configuration\Relation;
use Hateoas\Configuration\Route;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class SonataRelationProviderResolver implements RelationProviderResolverInterface
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param Pool $pool
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(Pool $pool, UrlGeneratorInterface $urlGenerator)
    {
        $this->pool = $pool;
    }

    /**
     * {@inheritDoc}
     */
    public function getRelationProvider(RelationProvider $relationProvider, $object)
    {
        if (false === $this->pool->hasAdminByClass(get_class($object))) {
            return null;
        }

        return array($this, 'getRelations');
    }

    /**
     * Return the sonata admin relations
     *
     * @return array
     */
    public function getRelations($object)
    {
        $admin = $this->pool->getAdminByClass(get_class($object));
        $relations = array();

        foreach ($admin->getRoutes() as $routeName => $route) {
            $relations[] = new Relation(
                $routeName,
                new Route(
                    $routeName,
                    array(
                        $admin->getIdParameter(), $admin->getUrlsafeIdentifier($object)
                    )
                )
            );
        }

        return $relations;
    }
}
