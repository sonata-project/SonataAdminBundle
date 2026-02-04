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

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Translator\NoopLabelTranslatorStrategy;

final class NoopLabelTranslatorStrategyTest extends TestCase
{
    public function testLabel(): void
    {
        $strategy = new NoopLabelTranslatorStrategy();

        static::assertSame('isValid', $strategy->getLabel('isValid', 'form', 'label'));
        static::assertSame('isValid_SuperCool', $strategy->getLabel('isValid_SuperCool', 'form', 'label'));
    }
}
