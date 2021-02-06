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

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @method TypeGuess|null guessTypeForFieldDescription(FieldDescriptionInterface $fieldDescription)
 */
interface TypeGuesserInterface
{
    /**
     * @deprecated since sonata-project/admin-bundle 3.x. Use guessTypeForFieldDescription instead.
     *
     * @param string $class
     * @param string $property
     *
     * @return TypeGuess|null
     *
     * @phpstan-param class-string $class
     */
    public function guessType($class, $property, ModelManagerInterface $modelManager);

    // NEXT_MAJOR: Uncomment next line.
    // public function guessTypeForFieldDescription(FieldDescriptionInterface $fieldDescription): ?TypeGuess;
}
