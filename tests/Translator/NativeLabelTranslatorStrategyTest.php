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
use Sonata\AdminBundle\Translator\NativeLabelTranslatorStrategy;

final class NativeLabelTranslatorStrategyTest extends TestCase
{
    /**
     * @dataProvider getLabelTests
     */
    public function testLabel(string $expectedLabel, string $label): void
    {
        $strategy = new NativeLabelTranslatorStrategy();

        self::assertSame($expectedLabel, $strategy->getLabel($label, 'form', 'label'));
    }

    /**
     * @phpstan-return array<array{string, string}>
     */
    public function getLabelTests(): array
    {
        return [
            ['Is Valid', 'isValid'],
            ['Is Valid', 'is_Valid'],
            ['Is0 Valid', 'is0Valid'],
            ['Is Valid', '_isValid'],
            ['Is Valid', '__isValid'],
            ['Is Valid', 'isValid_'],
            ['Is Valid', 'isValid__'],
            ['Is Valid', '__isValid__'],
            ['Is Valid Super Cool', 'isValid_SuperCool'],
        ];
    }
}
