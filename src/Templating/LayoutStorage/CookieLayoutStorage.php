<?php

namespace Sonata\AdminBundle\Templating\LayoutStorage;

use Symfony\Component\HttpFoundation\RequestStack;

final class CookieLayoutStorage implements LayoutStorageInterface
{
    private const COOKIE_NAME = 'sonata_admin_layout_code';

    public function __construct(private RequestStack $requestStack)
    {
    }

    public function get(): string
    {
        return $this->requestStack->getCurrentRequest()?->cookies?->get(self::COOKIE_NAME, 'default') ?? 'default';
    }

    public function set(string $layout): void
    {
        $this->requestStack->getCurrentRequest()?->cookies?->set(self::COOKIE_NAME, $layout);
    }
}
