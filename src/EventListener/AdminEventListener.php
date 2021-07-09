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

namespace Sonata\AdminBundle\EventListener;

use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * @author Christian Gripp <mail@core23.de>
 */
final class AdminEventListener implements EventSubscriberInterface
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var AdminFetcherInterface
     */
    private $adminFetcher;

    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    public function __construct(
        Environment $twig,
        AdminFetcherInterface $adminFetcher,
        TemplateRegistryInterface $templateRegistry
    ) {
        $this->twig = $twig;
        $this->adminFetcher = $adminFetcher;
        $this->templateRegistry = $templateRegistry;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', -50]],
        ];
    }

    public function onKernelRequest(KernelEvent $event): void
    {
        $request = $event->getRequest();

        try {
            $admin = $this->adminFetcher->get($request);
        } catch (\InvalidArgumentException $exception) {
            return;
        }

        $this->addVariable('admin', $admin);

        if ($this->isXmlHttpRequest($request)) {
            $baseTemplate = $this->templateRegistry->getTemplate('ajax');
        } else {
            $baseTemplate = $this->templateRegistry->getTemplate('layout');
        }

        $this->addVariable('base_template', $baseTemplate);
    }

    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * @return bool True if the request is an XMLHttpRequest, false otherwise
     */
    private function isXmlHttpRequest(Request $request): bool
    {
        if ($request->isXmlHttpRequest()) {
            return true;
        }

        return null !== $request->attributes->get('_xml_http_request');
    }

    /**
     * @param mixed $value
     */
    private function addVariable(string $name, $value): void
    {
        try {
            $this->twig->addGlobal($name, $value);
        } catch (\LogicException $exception) {
        }
    }
}
