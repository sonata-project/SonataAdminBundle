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

namespace Sonata\AdminBundle\Translator;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class NativeLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
    public function getLabel(string $label, string $context = '', string $type = ''): string
    {
        $label = str_replace(['_', '.'], ' ', $label);
        $label = strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $label) ?? '');

        return trim(ucwords(str_replace('_', ' ', $label)));
    }
}
