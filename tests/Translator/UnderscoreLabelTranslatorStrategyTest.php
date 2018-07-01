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
use Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy;

class UnderscoreLabelTranslatorStrategyTest extends TestCase
{
    public function testLabel(): void
    {
        $strategy = new UnderscoreLabelTranslatorStrategy();

        $this->assertSame('datagrid.label_is_valid', $strategy->getLabel('isValid', 'datagrid', 'label'));
        $this->assertSame('breadcrumb.link_is0_valid', $strategy->getLabel('is0Valid', 'breadcrumb', 'link'));
    }
}
