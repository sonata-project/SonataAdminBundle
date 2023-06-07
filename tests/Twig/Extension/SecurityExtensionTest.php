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

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Twig\Extension\SecurityExtension;
use Sonata\AdminBundle\Twig\SecurityRuntime;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * NEXT_MAJOR: Remove this test.
 *
 * @group legacy
 */
final class SecurityExtensionTest extends TestCase
{
    /**
     * @psalm-suppress DeprecatedMethod
     */
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
