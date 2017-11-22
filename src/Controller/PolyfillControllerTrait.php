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
    protected $container;

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
    protected function proxyToControllerClass($methodName, $arguments)
    {
        $reflectionClass = new \ReflectionClass(Controller::class);

        if (!$reflectionClass->hasMethod($methodName)) {
            throw new \LogicException('Call to undefined method '.__CLASS__.'::'.$methodName);
        }

        $controller = $reflectionClass->newInstance();
        $controller->setContainer($this->container);
        $method = $reflectionClass->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($controller, $arguments);
    }
}

if (!trait_exists(ControllerTrait::class)) {
    class_alias(PolyfillControllerTrait::class, ControllerTrait::class);
}
