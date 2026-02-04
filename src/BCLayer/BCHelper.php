<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\BCLayer;

use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final class BCHelper
{
    /**
     * Simulate the Symfony deprecated method Request::get.
     *
     * Should be used a few as possible, but migration is not easy...
     */
    public static function getFromRequest(Request $request, string $key, mixed $default = null): mixed
    {
        $result = $request->attributes->get($key, $request);
        if ($request !== $result) {
            return $result;
        }
        if ($request->query->has($key)) {
            return $request->query->all()[$key];
        }
        if ($request->request->has($key)) {
            return $request->request->all()[$key];
        }

        return $default;
    }
}
