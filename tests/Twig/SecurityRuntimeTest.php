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

namespace SensioLabs\AdminBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Twig\SecurityRuntime;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class SecurityRuntimeTest extends TestCase
{
    public function testIsGrantedAffirmative(): void
    {
        $securityChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $securityRuntime = new SecurityRuntime($securityChecker);

        $securityChecker
            ->method('isGranted')
            ->willReturnMap([
                ['foo', null, false],
                ['bar', null, true],
            ]);

        static::assertTrue($securityRuntime->isGrantedAffirmative(['foo', 'bar']));
        static::assertFalse($securityRuntime->isGrantedAffirmative('foo'));
        static::assertTrue($securityRuntime->isGrantedAffirmative('bar'));
    }
}
