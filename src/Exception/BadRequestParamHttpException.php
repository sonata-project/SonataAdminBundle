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

namespace Sonata\AdminBundle\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class BadRequestParamHttpException extends BadRequestHttpException
{
    /**
     * @param string|string[] $expectedTypes
     */
    public function __construct(
        string $name,
        $expectedTypes,
        mixed $value,
    ) {
        if (!\is_array($expectedTypes)) {
            $expectedTypes = [$expectedTypes];
        }

        $message = \sprintf(
            'Expected request parameter "%s" of type "%s", %s given',
            $name,
            implode('|', $expectedTypes),
            \is_object($value) ? 'instance of "'.$value::class.'"' : '"'.\gettype($value).'"'
        );

        parent::__construct($message);
    }
}
