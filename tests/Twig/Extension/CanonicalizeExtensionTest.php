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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Twig\CanonicalizeRuntime;
use Sonata\AdminBundle\Twig\Extension\CanonicalizeExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * NEXT_MAJOR: Remove this test.
 */
#[IgnoreDeprecations]
final class CanonicalizeExtensionTest extends TestCase
{
    private Request $request;

    private CanonicalizeExtension $twigExtension;

    protected function setUp(): void
    {
        $this->request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $this->twigExtension = new CanonicalizeExtension(
            new CanonicalizeRuntime($requestStack),
        );
    }

    #[DataProvider('provideCanonicalizedLocaleForSelect2Cases')]
    public function testCanonicalizedLocaleForSelect2(?string $expected, string $original): void
    {
        $this->changeLocale($original);
        static::assertSame($expected, $this->twigExtension->getCanonicalizedLocaleForSelect2());
    }

    /**
     * @return iterable<array{string|null, string}>
     */
    public static function provideCanonicalizedLocaleForSelect2Cases(): iterable
    {
        yield ['ar', 'ar'];
        yield ['az', 'az'];
        yield ['bg', 'bg'];
        yield ['ca', 'ca'];
        yield ['cs', 'cs'];
        yield ['da', 'da'];
        yield ['de', 'de'];
        yield ['el', 'el'];
        yield [null, 'en'];
        yield ['es', 'es'];
        yield ['et', 'et'];
        yield ['eu', 'eu'];
        yield ['fa', 'fa'];
        yield ['fi', 'fi'];
        yield ['fr', 'fr'];
        yield ['gl', 'gl'];
        yield ['he', 'he'];
        yield ['hr', 'hr'];
        yield ['hu', 'hu'];
        yield ['id', 'id'];
        yield ['is', 'is'];
        yield ['it', 'it'];
        yield ['ja', 'ja'];
        yield ['ka', 'ka'];
        yield ['ko', 'ko'];
        yield ['lt', 'lt'];
        yield ['lv', 'lv'];
        yield ['mk', 'mk'];
        yield ['ms', 'ms'];
        yield ['nb', 'nb'];
        yield ['nl', 'nl'];
        yield ['pl', 'pl'];
        yield ['pt-PT', 'pt'];
        yield ['pt-BR', 'pt-BR'];
        yield ['pt-PT', 'pt-PT'];
        yield ['ro', 'ro'];
        yield ['rs', 'rs'];
        yield ['ru', 'ru'];
        yield ['sk', 'sk'];
        yield ['sv', 'sv'];
        yield ['th', 'th'];
        yield ['tr', 'tr'];
        yield ['ug-CN', 'ug'];
        yield ['ug-CN', 'ug-CN'];
        yield ['uk', 'uk'];
        yield ['vi', 'vi'];
        yield ['zh-CN', 'zh'];
        yield ['zh-CN', 'zh-CN'];
        yield ['zh-TW', 'zh-TW'];
    }

    private function changeLocale(string $locale): void
    {
        $this->request->setLocale($locale);
    }
}
