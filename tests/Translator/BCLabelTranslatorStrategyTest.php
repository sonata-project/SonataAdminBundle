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

namespace SensioLabs\AdminBundle\Tests\Translator;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Translator\BCLabelTranslatorStrategy;

/**
 * NEXT_MAJOR: Remove this class.
 */
#[IgnoreDeprecations]
final class BCLabelTranslatorStrategyTest extends TestCase
{
    public function testLabel(): void
    {
        $strategy = new BCLabelTranslatorStrategy();

        static::assertSame('Isvalid', $strategy->getLabel('isValid', 'form', 'label'));
        static::assertSame('Plainpassword', $strategy->getLabel('plainPassword', 'form', 'label'));

        static::assertSame('breadcrumb.link_projectversion_list', $strategy->getLabel('ProjectVersion_list', 'breadcrumb', 'link'));
    }
}
