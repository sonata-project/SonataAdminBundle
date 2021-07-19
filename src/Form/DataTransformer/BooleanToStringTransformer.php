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

namespace Sonata\AdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * This is analog of Symfony\Component\Form\Extension\Core\DataTransformer\BooleanToStringTransformer
 * which allows you to use non-strings in reverseTransform() method.
 *
 * @author Peter Gribanov <info@peter-gribanov.ru>
 */
final class BooleanToStringTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $trueValue;

    public function __construct(string $trueValue)
    {
        $this->trueValue = $trueValue;
    }

    /**
     * @param bool|null $value
     */
    public function transform($value): ?string
    {
        return true === $value ? $this->trueValue : null;
    }

    /**
     * @param string|null $value
     */
    public function reverseTransform($value): bool
    {
        return filter_var($value, \FILTER_VALIDATE_BOOLEAN);
    }
}
