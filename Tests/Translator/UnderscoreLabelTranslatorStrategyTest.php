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

use Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy;

class UnderscoreLabelTranslatorStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testLabel()
    {
        $strategy = new UnderscoreLabelTranslatorStrategy();

        $this->assertSame('datagrid.label_is_valid', $strategy->getLabel('isValid', 'datagrid', 'label'));
        $this->assertSame('breadcrumb.link_is0_valid', $strategy->getLabel('is0Valid', 'breadcrumb', 'link'));
    }
}
