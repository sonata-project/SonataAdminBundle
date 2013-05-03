<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\AdminBundle\Tests\Translator;

use Sonata\AdminBundle\Translator\NativeLabelTranslatorStrategy;

class NativeTranslatorStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testLabel()
    {
        $strategy = new NativeLabelTranslatorStrategy;

        $this->assertEquals('Is Valid', $strategy->getLabel('isValid', 'form', 'label'));
        $this->assertEquals('Is Valid', $strategy->getLabel('is_Valid', 'form', 'label'));
        $this->assertEquals('Is0 Valid', $strategy->getLabel('is0Valid', 'form', 'label'));
        $this->assertEquals('Is Valid Super Cool', $strategy->getLabel('isValid_SuperCool', 'form', 'label'));
    }
}
