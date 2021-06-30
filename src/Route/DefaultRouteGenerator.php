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

namespace Sonata\AdminBundle\Route;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class DefaultRouteGenerator implements RouteGeneratorInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RoutesCache
     */
    private $cache;

    /**
     * @var array<string, string>
     */
    private $caches = [];

    /**
     * @var string[]
     */
    private $loaded = [];

    public function __construct(RouterInterface $router, RoutesCache $cache)
    {
        $this->router = $router;
        $this->cache = $cache;
    }

    public function generate(string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->router->generate($name, $parameters, $referenceType);
    }

    public function generateUrl(
        AdminInterface $admin,
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        $arrayRoute = $this->generateMenuUrl($admin, $name, $parameters, $referenceType);

        return $this->router->generate(
            $arrayRoute['route'],
            $arrayRoute['routeParameters'],
            $arrayRoute['routeAbsolute'] ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }

    public function generateMenuUrl(
        AdminInterface $admin,
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): array {
        // if the admin is a child we automatically append the parent's id
        if ($admin->isChild() && $admin->hasRequest()) {
            // twig template does not accept variable hash key ... so cannot use admin.idparameter ...
            // switch value
            if (isset($parameters['id'])) {
                $parameters[$admin->getIdParameter()] = $parameters['id'];
                unset($parameters['id']);
            }

            $parentAdmin = $admin->getParent();
            while (null !== $parentAdmin) {
                $parameters[$parentAdmin->getIdParameter()] = $admin->getRequest()->attributes->get($parentAdmin->getIdParameter());
                $parentAdmin = $parentAdmin->isChild() ? $parentAdmin->getParent() : null;
            }
        }

        // if the admin is linked to a parent FieldDescription (ie, embedded widget)
        if ($admin->hasParentFieldDescription()) {
            /** @var array<string, mixed> $linkParameters */
            $linkParameters = $admin->getParentFieldDescription()->getOption('link_parameters', []);
            // merge link parameter if any provided by the parent field
            $parameters = array_merge($parameters, $linkParameters);

            $parameters['uniqid'] = $admin->getUniqId();
            $parameters['code'] = $admin->getCode();
            $parameters['pcode'] = $admin->getParentFieldDescription()->getAdmin()->getCode();
            $parameters['puniqid'] = $admin->getParentFieldDescription()->getAdmin()->getUniqId();
        }

        if ('update' === $name || '|update' === substr($name, -7)) {
            $parameters['uniqid'] = $admin->getUniqId();
            $parameters['code'] = $admin->getCode();
        }

        // allows to define persistent parameters
        if ($admin->hasRequest()) {
            $parameters = array_merge($admin->getPersistentParameters(), $parameters);
        }

        $code = $this->getCode($admin, $name);

        if (!\array_key_exists($code, $this->caches)) {
            throw new \RuntimeException(sprintf('unable to find the route `%s`', $code));
        }

        return [
            'route' => $this->caches[$code],
            'routeParameters' => $parameters,
            'routeAbsolute' => UrlGeneratorInterface::ABSOLUTE_URL === $referenceType,
        ];
    }

    public function hasAdminRoute(AdminInterface $admin, string $name): bool
    {
        return \array_key_exists($this->getCode($admin, $name), $this->caches);
    }

    /**
     * @param AdminInterface<object> $admin
     */
    private function getCode(AdminInterface $admin, string $name): string
    {
        $this->loadCache($admin);

        // someone provide the fullname
        if (!$admin->isChild() && \array_key_exists($name, $this->caches)) {
            return $name;
        }

        $codePrefix = $admin->getBaseCodeRoute();

        // someone provide a code, so it is a child
        if (strpos($name, '.') > 0) {
            return sprintf('%s|%s', $codePrefix, $name);
        }

        return sprintf('%s.%s', $codePrefix, $name);
    }

    /**
     * @param AdminInterface<object> $admin
     */
    private function loadCache(AdminInterface $admin): void
    {
        if ($admin->isChild()) {
            $this->loadCache($admin->getParent());

            return;
        }

        if (\in_array($admin->getCode(), $this->loaded, true)) {
            return;
        }

        $this->caches = array_merge($this->cache->load($admin), $this->caches);

        $this->loaded[] = $admin->getCode();
    }
}
