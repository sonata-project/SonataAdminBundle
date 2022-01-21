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

namespace Sonata\AdminBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Twig\SecurityRuntime;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class SecurityRuntimeTest extends TestCase
{
    public function testIsGrantedAffirmative(): void
    {
        $securityChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $securityRuntime = new SecurityRuntime($securityChecker);

        $securityChecker
            ->method('isGranted')
            ->withConsecutive(
                ['foo', null],
                ['bar', null],
                ['foo', null],
                ['bar', null]
            )
            ->willReturnMap([
                ['foo', null, false],
                ['bar', null, true],
            ]);

        static::assertTrue($securityRuntime->isGrantedAffirmative(['foo', 'bar']));
        static::assertFalse($securityRuntime->isGrantedAffirmative('foo'));
        static::assertTrue($securityRuntime->isGrantedAffirmative('bar'));
    }
}
