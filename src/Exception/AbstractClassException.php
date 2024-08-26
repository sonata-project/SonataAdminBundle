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

/**
 * @author Morgan Abraham <morgan@geekimo.me>
 */
final class AbstractClassException extends \InvalidArgumentException
{
    /**
     * @param class-string $class
     */
    public function __construct(string $class)
    {
        parent::__construct(\sprintf('Cannot initialize abstract class: %s', $class));
    }
}
