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

namespace Sonata\AdminBundle\Util;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author Willem Verspyck
 */
final class ParametersManipulator
{
    /**
     * Merge parameters, but replace them when it's a subarray.
     *
     * @param array $parameters
     * @param array $filters
     *
     * @return array
     */
    public static function merge(array $parameters, array $filters): array
    {
        foreach (array_intersect_key($parameters, $filters) as $key => $parameter) {
            if (\is_array($parameter)) {
                $parameters[$key] = array_replace($parameter, $filters[$key]);
            } else {
                $parameters[$key] = $filters[$key];
            }
        }

        return array_merge($parameters, array_diff_key($filters, $parameters));
    }
}
