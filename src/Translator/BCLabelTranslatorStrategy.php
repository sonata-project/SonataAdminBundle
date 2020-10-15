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
final class BCLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
    /**
     * @param string $label
     * @param string $context
     * @param string $type
     *
     * @return string
     */

    public function getLabel(string $label, string $context = '', string $type = ''): string
    {
        if ('breadcrumb' === $context) {
            return sprintf('%s.%s_%s', $context, $type, strtolower($label));
        }

        return ucfirst(strtolower($label));
    }
}
