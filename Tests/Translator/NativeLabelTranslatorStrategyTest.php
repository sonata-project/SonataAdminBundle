<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Translator;

use Sonata\AdminBundle\Translator\NativeLabelTranslatorStrategy;

class NativeLabelTranslatorStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getLabelTests
     */
    public function testLabel($expectedLabel, $label)
    {
        $strategy = new NativeLabelTranslatorStrategy();

        $this->assertSame($expectedLabel, $strategy->getLabel($label, 'form', 'label'));
    }

    public function getLabelTests()
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
