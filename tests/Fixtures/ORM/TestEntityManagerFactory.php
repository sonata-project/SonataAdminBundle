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

namespace SensioLabs\AdminBundle\Tests\Fixtures\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\TestCase;

final class TestEntityManagerFactory
{
    public static function create(): EntityManagerInterface
    {
        if (!\extension_loaded('pdo_sqlite')) {
            TestCase::markTestSkipped('Extension pdo_sqlite is required.');
        }

        if (version_compare(\PHP_VERSION, '8.0.0', '>=')) {
            /* @phpstan-ignore function.alreadyNarrowedType */
            if (\PHP_VERSION_ID >= 80400 && method_exists(ORMSetup::class, 'createAttributeMetadataConfig')) {
                $config = ORMSetup::createAttributeMetadataConfig([], true);
            } else {
                $config = ORMSetup::createAttributeMetadataConfiguration([], true);
            }
        } else {
            /**
             * @var Configuration $config
             *
             * @phpstan-ignore-next-line
             */
            $config = ORMSetup::createAnnotationMetadataConfiguration([], true);
        }

        if (\PHP_VERSION_ID >= 80400) {
            $config->enableNativeLazyObjects(true);
        }

        $connection = DriverManager::getConnection(
            [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ],
            $config
        );

        return new EntityManager(
            $connection,
            $config,
            new EventManager()
        );
    }
}
