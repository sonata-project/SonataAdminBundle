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

namespace Sonata\AdminBundle\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;

class NoopLabelTranslatorStrategyTest extends TestCase
{
    public function testLabel(): void
    {
        $strategy = new NoopLabelTranslatorStrategy();

        $this->assertSame('isValid', $strategy->getLabel('isValid', 'form', 'label'));
        $this->assertSame('isValid_SuperCool', $strategy->getLabel('isValid_SuperCool', 'form', 'label'));
    }
}
