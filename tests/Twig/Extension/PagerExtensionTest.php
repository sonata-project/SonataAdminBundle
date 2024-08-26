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
use Sonata\AdminBundle\Twig\Extension\PagerExtension;

final class PagerExtensionTest extends TestCase
{
    public function testExposesTheDeterministicFunction(): void
    {
        $functionNames = array_map(
            static fn ($function): string => $function->getName(),
            (new PagerExtension())->getFunctions(),
        );

        static::assertContains('sonata_pager_deterministic', $functionNames);
    }
}
