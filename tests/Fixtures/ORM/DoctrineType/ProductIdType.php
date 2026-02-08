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
use Doctrine\DBAL\Types\Type;
use SensioLabs\AdminBundle\Tests\Fixtures\ORM\Entity\ProductId;

final class ProductIdType extends Type
{
    public const NAME = 'ProductId';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?ProductId
    {
        if (null === $value || $value instanceof ProductId) {
            return $value;
        }

        if (!is_numeric($value)) {
            throw new \RuntimeException('Invalid value: '.$value);
        }

        return new ProductId((int) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?int
    {
        $value = $this->convertToPHPValue($value, $platform);

        return null !== $value ? $value->getId() : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
