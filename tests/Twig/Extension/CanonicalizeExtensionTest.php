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
use Sonata\AdminBundle\Twig\Extension\CanonicalizeExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class CanonicalizeExtensionTest extends TestCase
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var CanonicalizeExtension
     */
    private $twigExtension;

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();
        $this->requestStack->push(new Request());
        $this->twigExtension = new CanonicalizeExtension($this->requestStack);
    }

    /**
     * @dataProvider momentLocalesProvider
     */
    public function testCanonicalizedLocaleForMoment(?string $expected, string $original): void
    {
        $this->changeLocale($original);
        $this->assertSame($expected, $this->twigExtension->getCanonicalizedLocaleForMoment());
    }

    /**
     * @dataProvider select2LocalesProvider
     */
    public function testCanonicalizedLocaleForSelect2(?string $expected, string $original): void
    {
        $this->changeLocale($original);
        $this->assertSame($expected, $this->twigExtension->getCanonicalizedLocaleForSelect2());
    }

    /**
     * @return array<array{?string, string}>
     */
    public function momentLocalesProvider(): array
    {
        return [
            ['af', 'af'],
            ['ar-dz', 'ar-dz'],
            ['ar', 'ar'],
            ['ar-ly', 'ar-ly'],
            ['ar-ma', 'ar-ma'],
            ['ar-sa', 'ar-sa'],
            ['ar-tn', 'ar-tn'],
            ['az', 'az'],
            ['be', 'be'],
            ['bg', 'bg'],
            ['bn', 'bn'],
            ['bo', 'bo'],
            ['br', 'br'],
            ['bs', 'bs'],
            ['ca', 'ca'],
            ['cs', 'cs'],
            ['cv', 'cv'],
            ['cy', 'cy'],
            ['da', 'da'],
            ['de-at', 'de-at'],
            ['de', 'de'],
            ['de', 'de-de'],
            ['dv', 'dv'],
            ['el', 'el'],
            [null, 'en'],
            [null, 'en-us'],
            ['en-au', 'en-au'],
            ['en-ca', 'en-ca'],
            ['en-gb', 'en-gb'],
            ['en-ie', 'en-ie'],
            ['en-nz', 'en-nz'],
            ['eo', 'eo'],
            ['es-do', 'es-do'],
            ['es', 'es-ar'],
            ['es', 'es-mx'],
            ['es', 'es'],
            ['et', 'et'],
            ['eu', 'eu'],
            ['fa', 'fa'],
            ['fi', 'fi'],
            ['fo', 'fo'],
            ['fr-ca', 'fr-ca'],
            ['fr-ch', 'fr-ch'],
            ['fr', 'fr-fr'],
            ['fr', 'fr'],
            ['fy', 'fy'],
            ['gd', 'gd'],
            ['gl', 'gl'],
            ['he', 'he'],
            ['hi', 'hi'],
            ['hr', 'hr'],
            ['hu', 'hu'],
            ['hy-am', 'hy-am'],
            ['id', 'id'],
            ['is', 'is'],
            ['it', 'it'],
            ['ja', 'ja'],
            ['jv', 'jv'],
            ['ka', 'ka'],
            ['kk', 'kk'],
            ['km', 'km'],
            ['ko', 'ko'],
            ['ky', 'ky'],
            ['lb', 'lb'],
            ['lo', 'lo'],
            ['lt', 'lt'],
            ['lv', 'lv'],
            ['me', 'me'],
            ['mi', 'mi'],
            ['mk', 'mk'],
            ['ml', 'ml'],
            ['mr', 'mr'],
            ['ms', 'ms'],
            ['ms-my', 'ms-my'],
            ['my', 'my'],
            ['nb', 'nb'],
            ['ne', 'ne'],
            ['nl-be', 'nl-be'],
            ['nl', 'nl'],
            ['nl', 'nl-nl'],
            ['nn', 'nn'],
            ['pa-in', 'pa-in'],
            ['pl', 'pl'],
            ['pt-br', 'pt-br'],
            ['pt', 'pt'],
            ['ro', 'ro'],
            ['ru', 'ru'],
            ['se', 'se'],
            ['si', 'si'],
            ['sk', 'sk'],
            ['sl', 'sl'],
            ['sq', 'sq'],
            ['sr-cyrl', 'sr-cyrl'],
            ['sr', 'sr'],
            ['ss', 'ss'],
            ['sv', 'sv'],
            ['sw', 'sw'],
            ['ta', 'ta'],
            ['te', 'te'],
            ['tet', 'tet'],
            ['th', 'th'],
            ['tlh', 'tlh'],
            ['tl-ph', 'tl-ph'],
            ['tr', 'tr'],
            ['tzl', 'tzl'],
            ['tzm', 'tzm'],
            ['tzm-latn', 'tzm-latn'],
            ['uk', 'uk'],
            ['uz', 'uz'],
            ['vi', 'vi'],
            ['x-pseudo', 'x-pseudo'],
            ['yo', 'yo'],
            ['zh-cn', 'zh-cn'],
            ['zh-hk', 'zh-hk'],
            ['zh-tw', 'zh-tw'],
        ];
    }

    /**
     * @return array<array{?string, string}>
     */
    public function select2LocalesProvider()
    {
        return [
            ['ar', 'ar'],
            ['az', 'az'],
            ['bg', 'bg'],
            ['ca', 'ca'],
            ['cs', 'cs'],
            ['da', 'da'],
            ['de', 'de'],
            ['el', 'el'],
            [null, 'en'],
            ['es', 'es'],
            ['et', 'et'],
            ['eu', 'eu'],
            ['fa', 'fa'],
            ['fi', 'fi'],
            ['fr', 'fr'],
            ['gl', 'gl'],
            ['he', 'he'],
            ['hr', 'hr'],
            ['hu', 'hu'],
            ['id', 'id'],
            ['is', 'is'],
            ['it', 'it'],
            ['ja', 'ja'],
            ['ka', 'ka'],
            ['ko', 'ko'],
            ['lt', 'lt'],
            ['lv', 'lv'],
            ['mk', 'mk'],
            ['ms', 'ms'],
            ['nb', 'nb'],
            ['nl', 'nl'],
            ['pl', 'pl'],
            ['pt-PT', 'pt'],
            ['pt-BR', 'pt-BR'],
            ['pt-PT', 'pt-PT'],
            ['ro', 'ro'],
            ['rs', 'rs'],
            ['ru', 'ru'],
            ['sk', 'sk'],
            ['sv', 'sv'],
            ['th', 'th'],
            ['tr', 'tr'],
            ['ug-CN', 'ug'],
            ['ug-CN', 'ug-CN'],
            ['uk', 'uk'],
            ['vi', 'vi'],
            ['zh-CN', 'zh'],
            ['zh-CN', 'zh-CN'],
            ['zh-TW', 'zh-TW'],
        ];
    }

    private function changeLocale(string $locale): void
    {
        $this->requestStack->getCurrentRequest()->setLocale($locale);
    }
}
