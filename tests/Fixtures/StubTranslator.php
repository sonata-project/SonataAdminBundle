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

namespace Sonata\AdminBundle\Tests\Fixtures;

use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

if (interface_exists(TranslatorInterface::class)) {
    final class StubTranslator implements TranslatorInterface
    {
        public function trans($id, array $parameters = [], $domain = null, $locale = null): string
        {
            return '[trans]'.strtr($id, $parameters).'[/trans]';
        }
    }
} else {
    final class StubTranslator implements LegacyTranslatorInterface
    {
        public function trans($id, array $parameters = [], $domain = null, $locale = null)
        {
            return '[trans]'.$id.'[/trans]';
        }

        public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
        {
            return '[trans]'.$id.'[/trans]';
        }

        public function setLocale($locale)
        {
        }

        public function getLocale()
        {
        }
    }
}
