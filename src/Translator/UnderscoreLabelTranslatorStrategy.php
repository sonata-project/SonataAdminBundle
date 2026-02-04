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

namespace SensioLabs\AdminBundle\Translator;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class UnderscoreLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
    public function getLabel(string $label, string $context = '', string $type = ''): string
    {
        $label = str_replace('.', '_', $label);

        return \sprintf('%s.%s_%s', $context, $type, strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $label) ?? ''));
    }
}
