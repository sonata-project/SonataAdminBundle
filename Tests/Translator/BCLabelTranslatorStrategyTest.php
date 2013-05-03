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

use Sonata\AdminBundle\Translator\BCLabelTranslatorStrategy;

class BCLabelTranslatorStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testLabel()
    {
        $strategy = new BCLabelTranslatorStrategy;

        $this->assertEquals('Isvalid', $strategy->getLabel('isValid', 'form', 'label'));
        $this->assertEquals('Plainpassword', $strategy->getLabel('plainPassword', 'form', 'label'));

        $this->assertEquals('breadcrumb.link_projectversion_list', $strategy->getLabel('ProjectVersion_list', 'breadcrumb', 'link'));
    }
}
