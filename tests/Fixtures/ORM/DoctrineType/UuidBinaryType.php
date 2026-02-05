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

namespace Sonata\DoctrineORMAdminBundle\Tests\Fixtures\DoctrineType;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use SensioLabs\AdminBundle\Tests\Fixtures\ORM\Util\NonIntegerIdentifierTestClass;

/**
 * Mock for a custom doctrine type used in the ModelManagerTest suite.
 *
 * @author Jorge Garces <jgarces@iberdat.com>
 */
final class UuidBinaryType extends StringType
{
    public const NAME = 'sonata_uuid_binary';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?NonIntegerIdentifierTestClass
    {
        if (null === $value || $value instanceof NonIntegerIdentifierTestClass) {
            return $value;
        }

        if (!\is_string($value)) {
            throw new \RuntimeException('Invalid value: '.$value);
        }

        return new NonIntegerIdentifierTestClass($value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        $value = $this->convertToPHPValue($value, $platform);

        if (null === $value) {
            return null;
        }

        $bin = hex2bin(str_replace('-', '', $value->toString()));
        if (false === $bin) {
            throw new \RuntimeException('Invalid value: '.var_export($value, true));
        }

        return $bin;
    }
}
