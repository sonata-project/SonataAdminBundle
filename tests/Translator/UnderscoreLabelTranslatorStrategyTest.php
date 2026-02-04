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
use SensioLabs\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy;

final class UnderscoreLabelTranslatorStrategyTest extends TestCase
{
    public function testLabel(): void
    {
        $strategy = new UnderscoreLabelTranslatorStrategy();

        static::assertSame('datagrid.label_is_valid', $strategy->getLabel('isValid', 'datagrid', 'label'));
        static::assertSame('breadcrumb.link_is0_valid', $strategy->getLabel('is0Valid', 'breadcrumb', 'link'));
    }
}
