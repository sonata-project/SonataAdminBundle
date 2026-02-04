<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Twig\Extension;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Twig\Extension\SecurityExtension;
use SensioLabs\AdminBundle\Twig\SecurityRuntime;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * NEXT_MAJOR: Remove this test.
 */
#[IgnoreDeprecations]
final class SecurityExtensionTest extends TestCase
{
    public function testIsGrantedAffirmative(): void
    {
        $securityChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $twigExtension = new SecurityExtension(new SecurityRuntime($securityChecker));

        $securityChecker
            ->method('isGranted')
            ->willReturnMap([
                ['foo', null, false],
                ['bar', null, true],
            ]);

        static::assertTrue($twigExtension->isGrantedAffirmative(['foo', 'bar']));
        static::assertFalse($twigExtension->isGrantedAffirmative('foo'));
        static::assertTrue($twigExtension->isGrantedAffirmative('bar'));
    }
}
