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

namespace SensioLabs\AdminBundle\Tests\Fixtures\ORM\DoctrineType;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use SensioLabs\AdminBundle\Tests\Fixtures\ORM\Util\NonIntegerIdentifierTestClass;

/**
 * Mock for a custom doctrine type used in the ModelManagerTest suite.
 *
 * @author Jeroen Thora <jeroen.thora@gmail.com>
 */
final class UuidType extends StringType
{
    public const NAME = 'sonata_uuid';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?NonIntegerIdentifierTestClass
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (!\is_string($value)) {
            throw new \RuntimeException('Invalid value: '.$value);
        }

        return new NonIntegerIdentifierTestClass($value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        $value = $this->convertToPHPValue($value, $platform);

        return null !== $value ? $value->toString() : null;
    }
}
