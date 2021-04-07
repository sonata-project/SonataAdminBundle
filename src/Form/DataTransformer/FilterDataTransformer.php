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

final class FilterDataTransformer implements DataTransformerInterface
{
    public function reverseTransform($value): FilterData
    {
        if (!\is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        return FilterData::fromArray($value);
    }

    /**
     * @param FilterData|null $value
     */
    public function transform($value)
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
