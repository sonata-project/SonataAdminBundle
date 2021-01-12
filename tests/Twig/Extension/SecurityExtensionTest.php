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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class SecurityExtensionTest extends TestCase
{
    public function testIsGrantedAffirmative(): void
    {
        $securityChecker = $this->createStub(AuthorizationCheckerInterface::class);
        $twigExtension = new SecurityExtension($securityChecker);

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

        $this->assertTrue($twigExtension->isGrantedAffirmative(['foo', 'bar']));
        $this->assertFalse($twigExtension->isGrantedAffirmative('foo'));
        $this->assertTrue($twigExtension->isGrantedAffirmative('bar'));
    }
}
