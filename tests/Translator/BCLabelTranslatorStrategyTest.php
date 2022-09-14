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
use Sonata\AdminBundle\Translator\BCLabelTranslatorStrategy;

/**
 * NEXT_MAJOR: Remove this class.
 *
 * @group legacy
 */
final class BCLabelTranslatorStrategyTest extends TestCase
{
    public function testLabel(): void
    {
        $strategy = new BCLabelTranslatorStrategy();

        static::assertSame('Isvalid', $strategy->getLabel('isValid', 'form', 'label'));
        static::assertSame('Plainpassword', $strategy->getLabel('plainPassword', 'form', 'label'));

        static::assertSame('breadcrumb.link_projectversion_list', $strategy->getLabel('ProjectVersion_list', 'breadcrumb', 'link'));
    }
}
