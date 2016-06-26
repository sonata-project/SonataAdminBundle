<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder\Exception;

/**
 * Signals that a field description is missing an association target model class when a form type requires it.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class MissingTargetModelClassException extends \LogicException
{
}
