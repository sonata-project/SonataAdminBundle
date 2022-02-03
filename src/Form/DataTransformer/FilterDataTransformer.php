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

use Sonata\AdminBundle\Filter\Model\FilterData;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * @phpstan-implements DataTransformerInterface<FilterData, array{type?: int|numeric-string|null, value?: mixed}>
 */
final class FilterDataTransformer implements DataTransformerInterface
{
    /**
     * @param array{type?: int|numeric-string|null, value?: mixed}|null $value
     */
    public function reverseTransform($value): FilterData
    {
        if (null === $value) {
            return FilterData::fromArray([]);
        }

        if (!\is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        return FilterData::fromArray($value);
    }

    /**
     * @param FilterData|null $value
     *
     * @return array{type: int|null, value?: mixed}|null
     */
    public function transform($value): ?array
    {
        if (null === $value) {
            return null;
        }

        $data = [
            'type' => $value->getType(),
        ];

        if ($value->hasValue()) {
            $data['value'] = $value->getValue();
        }

        return $data;
    }
}
