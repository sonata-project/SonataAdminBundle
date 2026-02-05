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

namespace SensioLabs\AdminBundle\FieldDescription\ORM;

use Doctrine\ORM\Mapping\ClassMetadata;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\FieldDescription\TypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

final class TypeGuesser implements TypeGuesserInterface
{
    public function guess(FieldDescriptionInterface $fieldDescription): TypeGuess
    {
        switch ($fieldDescription->getMappingType()) {
            case 'array':
            case 'simple_array':
            case 'json':
            case 'json_array':
                return new TypeGuess(FieldDescriptionInterface::TYPE_ARRAY, [], Guess::HIGH_CONFIDENCE);
            case 'boolean':
                return new TypeGuess(FieldDescriptionInterface::TYPE_BOOLEAN, [], Guess::HIGH_CONFIDENCE);
            case 'datetime':
            case 'datetime_immutable':
            case 'vardatetime':
            case 'datetimetz':
            case 'datetimetz_immutable':
                return new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE);
            case 'date':
            case 'date_immutable':
                return new TypeGuess(FieldDescriptionInterface::TYPE_DATE, [], Guess::HIGH_CONFIDENCE);
            case 'decimal':
            case 'float':
                return new TypeGuess(FieldDescriptionInterface::TYPE_FLOAT, [], Guess::MEDIUM_CONFIDENCE);
            case 'integer':
            case 'bigint':
            case 'smallint':
                return new TypeGuess(FieldDescriptionInterface::TYPE_INTEGER, [], Guess::MEDIUM_CONFIDENCE);
            case 'string':
                return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::MEDIUM_CONFIDENCE);
            case 'text':
                return new TypeGuess(FieldDescriptionInterface::TYPE_TEXTAREA, [], Guess::MEDIUM_CONFIDENCE);
            case 'time':
            case 'time_immutable':
                return new TypeGuess(FieldDescriptionInterface::TYPE_TIME, [], Guess::HIGH_CONFIDENCE);
            case 'enum':
                return new TypeGuess(FieldDescriptionInterface::TYPE_ENUM, [], Guess::HIGH_CONFIDENCE);
            case ClassMetadata::ONE_TO_MANY:
                return new TypeGuess(FieldDescriptionInterface::TYPE_ONE_TO_MANY, [], Guess::HIGH_CONFIDENCE);
            case ClassMetadata::MANY_TO_MANY:
                return new TypeGuess(FieldDescriptionInterface::TYPE_MANY_TO_MANY, [], Guess::HIGH_CONFIDENCE);
            case ClassMetadata::MANY_TO_ONE:
                return new TypeGuess(FieldDescriptionInterface::TYPE_MANY_TO_ONE, [], Guess::HIGH_CONFIDENCE);
            case ClassMetadata::ONE_TO_ONE:
                return new TypeGuess(FieldDescriptionInterface::TYPE_ONE_TO_ONE, [], Guess::HIGH_CONFIDENCE);
            default:
                return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::LOW_CONFIDENCE);
        }
    }
}
