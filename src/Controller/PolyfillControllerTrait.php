<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Polyfills the FrameworkBundle's original ControllerTrait.
 *
 * NEXT_MAJOR: Remove this file.
 *
 * @author Christian Kraus <hanzi@hanzi.cc>
 */
trait PolyfillControllerTrait
{
    public function __call($methodName, $arguments)
    {
        $this->proxyToController($methodName, $arguments);
    }

    public function render($view, array $parameters = [], Response $response = null)
    {
        return $this->__call('render', [$view, $parameters, $response]);
    }

    /**
     * @internal
     */
    final protected function proxyToControllerClass($methodName, $arguments)
    {
        if (!method_exists(Controller::class, $methodName)) {
            throw new \LogicException('Call to undefined method '.__CLASS__.'::'.$methodName);
        }

        $controller = new PolyfillProxyContainer($this->container);

        return $controller->proxyCall($methodName, $arguments);
    }
}

class PolyfillProxyContainer extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    public function proxyCall($method, $arguments)
    {
        return \call_user_func_array([$this, $method], $arguments);
    }
}

if (!trait_exists(ControllerTrait::class)) {
    class_alias(PolyfillControllerTrait::class, ControllerTrait::class);
}
