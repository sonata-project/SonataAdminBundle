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

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Translator\FormLabelTranslatorStrategy;

class FormLabelTranslatorStrategyTest extends TestCase
{
    public function testLabel()
    {
        $strategy = new FormLabelTranslatorStrategy();

        $this->assertSame('Isvalid', $strategy->getLabel('isValid', 'form', 'label'));
        $this->assertSame('Plainpassword', $strategy->getLabel('plainPassword', 'form', 'label'));
    }
}
