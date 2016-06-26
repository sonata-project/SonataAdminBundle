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
 * Signals that a form field cannot be used to handle a particular association type.
 *
 * For example, when a CollectionType is used with a singled-value association.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class UnhandledAssociationTypeException extends \LogicException
{
}
