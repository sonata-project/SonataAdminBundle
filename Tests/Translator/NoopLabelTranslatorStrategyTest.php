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

use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;

class NoopLabelTranslatorStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testLabel()
    {
        $strategy = new NoopLabelTranslatorStrategy;

        $this->assertEquals('isValid', $strategy->getLabel('isValid', 'form', 'label'));
        $this->assertEquals('isValid_SuperCool', $strategy->getLabel('isValid_SuperCool', 'form', 'label'));
    }
}
