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

namespace Sonata\AdminBundle\Helper;

use Sonata\AdminBundle\Helper\CsrfTokenManagerInterface as SonataCsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @author Wojciech Błoszyk <wbloszyk@gmail.com>
 */
final class CsrfTokenManager implements SonataCsrfTokenManagerInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var CsrfTokenManagerInterface|null
     */
    private $csrfTokenManager;

    public function __construct(RequestStack $requestStack, ?CsrfTokenManagerInterface $csrfTokenManager = null)
    {
        $this->requestStack = $requestStack;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function getCsrfToken(string $intention)
    {
        if (null === $this->csrfTokenManager) {
            return false;
        }

        return $this->csrfTokenManager->getToken($intention)->getValue();
    }

    /**
     * Validate CSRF token for action without form.
     *
     * @throws HttpException
     */
    public function validateCsrfToken(string $intention): void
    {
        if (null === $this->csrfTokenManager) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $token = $request->get('_sonata_csrf_token');

        $valid = $this->csrfTokenManager->isTokenValid(new CsrfToken($intention, $token));

        if (!$valid) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The csrf token is not valid, CSRF attack?');
        }
    }
}
