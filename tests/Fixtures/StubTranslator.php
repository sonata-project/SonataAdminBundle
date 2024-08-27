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

use Symfony\Contracts\Translation\TranslatorInterface;

final class StubTranslator implements TranslatorInterface
{
    /**
     * @param string      $id
     * @param mixed[]     $parameters
     * @param string|null $domain
     * @param string|null $locale
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null): string
    {
        $transOpeningTag = '[trans]';

        if (null !== $domain) {
            $transOpeningTag = \sprintf('[trans domain=%s]', $domain);
        }

        return $transOpeningTag.strtr($id, $parameters).'[/trans]';
    }

    public function getLocale(): string
    {
        return 'en';
    }
}
