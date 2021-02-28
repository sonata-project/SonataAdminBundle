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

namespace Sonata\AdminBundle\Guesser;

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * NEXT_MAJOR: Remove this class.
 *
 * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0.
 * Use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface instead.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface TypeGuesserInterface
{
    /**
     * @param string $class
     * @param string $property
     *
     * @return TypeGuess|null
     *
     * @phpstan-param class-string $class
     */
    public function guessType($class, $property, ModelManagerInterface $modelManager);
}
