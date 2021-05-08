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

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Twig\Extension\IconExtension;
use Sonata\AdminBundle\Twig\Extension\XEditableExtension;
use Symfony\Component\Translation\Translator;

final class IconExtensionTest extends TestCase
{
    /**
     * @dataProvider iconProvider
     */
    public function testGetXEditableChoicesIsIdempotent(string $icon, bool $inline, string $expected): void
    {
        $twigExtension = new IconExtension();

        $this->assertSame($expected, $twigExtension->parseIcon($icon, $inline));
    }

    public function iconProvider(): iterable
    {
        //'icon' => '<i class="fa fa-cog"></i>',
        //'icon' => 'fa fa-cog',
        //'icon' => 'fa-cog',
        //'icon' => 'cog',
        return [
            'html input' => ['<i class="fa fa-cog"></i>', false ,'<i class="fa fa-cog"></i>'],
            'html input, inline' => ['<i class="fa fa-cog"></i>', true, 'fa fa-cog'],
            'with prefix' => ['<i class="fa fa-cog"></i>', false, '<i class="fa fa-cog"></i>'],
            'with prefix, inline' => ['<i class="fa fa-cog"></i>', true, 'fa fa-cog'],
            // NEXT_MAJOR: Remove next 4 tests cases.
            'icon-prefix only' => ['fa-cog', false, '<i class="fa fa-cog"></i>'],
            'icon-prefix only, inline' =>[ 'fa-cog', true, 'fa fa-cog'],
            'icon name' => ['cog', false, '<i class="fa fa-cog"></i>'],
            'icon name, inline' =>[ 'cog', true, 'fa fa-cog'],
        ];
    }
}
