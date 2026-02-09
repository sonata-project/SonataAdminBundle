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

namespace SensioLabs\AdminBundle\Form\DataTransformer;

use SensioLabs\AdminBundle\Form\Type\BooleanType;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @phpstan-implements DataTransformerInterface<bool, int>
 */
final class BooleanTypeToBooleanTransformer implements DataTransformerInterface
{
    /**
     * @phpstan-throws void
     *
     * @phpstan-param mixed $value
     */
    public function transform(mixed $value): ?int
    {
        if (true === $value || BooleanType::TYPE_YES === (int) $value) {
            return BooleanType::TYPE_YES;
        }
        if (false === $value || BooleanType::TYPE_NO === (int) $value) {
            return BooleanType::TYPE_NO;
        }

        return null;
    }

    /**
     * @phpstan-throws void
     *
     * @phpstan-param mixed $value
     */
    public function reverseTransform(mixed $value): ?bool
    {
        if (BooleanType::TYPE_YES === $value) {
            return true;
        }
        if (BooleanType::TYPE_NO === $value) {
            return false;
        }

        return null;
    }
}
