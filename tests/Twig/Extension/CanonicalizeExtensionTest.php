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
use Sonata\AdminBundle\Twig\CanonicalizeRuntime;
use Sonata\AdminBundle\Twig\Extension\CanonicalizeExtension;
use Sonata\Form\Twig\CanonicalizeRuntime as SonataFormCanonicalizeRuntime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * NEXT_MAJOR: Remove this test.
 *
 * @group legacy
 */
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
            new CanonicalizeRuntime(
                $requestStack,
                class_exists(SonataFormCanonicalizeRuntime::class) ? new SonataFormCanonicalizeRuntime($requestStack) : null,
            )
        );
    }

    /**
     * @dataProvider provideCanonicalizedLocaleForMomentCases
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function testCanonicalizedLocaleForMoment(?string $expected, string $original): void
    {
        $this->changeLocale($original);

        $expected = class_exists(SonataFormCanonicalizeRuntime::class) ? $expected : null;

        static::assertSame($expected, $this->twigExtension->getCanonicalizedLocaleForMoment());
    }

    /**
     * @dataProvider provideCanonicalizedLocaleForSelect2Cases
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function testCanonicalizedLocaleForSelect2(?string $expected, string $original): void
    {
        $this->changeLocale($original);
        static::assertSame($expected, $this->twigExtension->getCanonicalizedLocaleForSelect2());
    }

    /**
     * @return iterable<array{string|null, string}>
     */
    public function provideCanonicalizedLocaleForMomentCases(): iterable
    {
        yield ['af', 'af'];
        yield ['ar-dz', 'ar-dz'];
        yield ['ar', 'ar'];
        yield ['ar-ly', 'ar-ly'];
        yield ['ar-ma', 'ar-ma'];
        yield ['ar-sa', 'ar-sa'];
        yield ['ar-tn', 'ar-tn'];
        yield ['az', 'az'];
        yield ['be', 'be'];
        yield ['bg', 'bg'];
        yield ['bn', 'bn'];
        yield ['bo', 'bo'];
        yield ['br', 'br'];
        yield ['bs', 'bs'];
        yield ['ca', 'ca'];
        yield ['cs', 'cs'];
        yield ['cv', 'cv'];
        yield ['cy', 'cy'];
        yield ['da', 'da'];
        yield ['de-at', 'de-at'];
        yield ['de', 'de'];
        yield ['de', 'de-de'];
        yield ['dv', 'dv'];
        yield ['el', 'el'];
        yield [null, 'en'];
        yield [null, 'en-us'];
        yield ['en-au', 'en-au'];
        yield ['en-ca', 'en-ca'];
        yield ['en-gb', 'en-gb'];
        yield ['en-ie', 'en-ie'];
        yield ['en-nz', 'en-nz'];
        yield ['eo', 'eo'];
        yield ['es-do', 'es-do'];
        yield ['es', 'es-ar'];
        yield ['es', 'es-mx'];
        yield ['es', 'es'];
        yield ['et', 'et'];
        yield ['eu', 'eu'];
        yield ['fa', 'fa'];
        yield ['fi', 'fi'];
        yield ['fo', 'fo'];
        yield ['fr-ca', 'fr-ca'];
        yield ['fr-ch', 'fr-ch'];
        yield ['fr', 'fr-fr'];
        yield ['fr', 'fr'];
        yield ['fy', 'fy'];
        yield ['gd', 'gd'];
        yield ['gl', 'gl'];
        yield ['he', 'he'];
        yield ['hi', 'hi'];
        yield ['hr', 'hr'];
        yield ['hu', 'hu'];
        yield ['hy-am', 'hy-am'];
        yield ['id', 'id'];
        yield ['is', 'is'];
        yield ['it', 'it'];
        yield ['ja', 'ja'];
        yield ['jv', 'jv'];
        yield ['ka', 'ka'];
        yield ['kk', 'kk'];
        yield ['km', 'km'];
        yield ['ko', 'ko'];
        yield ['ky', 'ky'];
        yield ['lb', 'lb'];
        yield ['lo', 'lo'];
        yield ['lt', 'lt'];
        yield ['lv', 'lv'];
        yield ['me', 'me'];
        yield ['mi', 'mi'];
        yield ['mk', 'mk'];
        yield ['ml', 'ml'];
        yield ['mr', 'mr'];
        yield ['ms', 'ms'];
        yield ['ms-my', 'ms-my'];
        yield ['my', 'my'];
        yield ['nb', 'nb'];
        yield ['ne', 'ne'];
        yield ['nl-be', 'nl-be'];
        yield ['nl', 'nl'];
        yield ['nl', 'nl-nl'];
        yield ['nn', 'nn'];
        yield ['pa-in', 'pa-in'];
        yield ['pl', 'pl'];
        yield ['pt-br', 'pt-br'];
        yield ['pt', 'pt'];
        yield ['ro', 'ro'];
        yield ['ru', 'ru'];
        yield ['se', 'se'];
        yield ['si', 'si'];
        yield ['sk', 'sk'];
        yield ['sl', 'sl'];
        yield ['sq', 'sq'];
        yield ['sr-cyrl', 'sr-cyrl'];
        yield ['sr', 'sr'];
        yield ['ss', 'ss'];
        yield ['sv', 'sv'];
        yield ['sw', 'sw'];
        yield ['ta', 'ta'];
        yield ['te', 'te'];
        yield ['tet', 'tet'];
        yield ['th', 'th'];
        yield ['tlh', 'tlh'];
        yield ['tl-ph', 'tl-ph'];
        yield ['tr', 'tr'];
        yield ['tzl', 'tzl'];
        yield ['tzm', 'tzm'];
        yield ['tzm-latn', 'tzm-latn'];
        yield ['uk', 'uk'];
        yield ['uz', 'uz'];
        yield ['vi', 'vi'];
        yield ['x-pseudo', 'x-pseudo'];
        yield ['yo', 'yo'];
        yield ['zh-cn', 'zh-cn'];
        yield ['zh-hk', 'zh-hk'];
        yield ['zh-tw', 'zh-tw'];
    }

    /**
     * @return iterable<array{string|null, string}>
     */
    public function provideCanonicalizedLocaleForSelect2Cases(): iterable
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
