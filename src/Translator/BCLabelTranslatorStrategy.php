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
 *
 * NEXT_MAJOR: Remove this class.
 *
 * @deprecated since sonata-project/admin-bundle 4.19, will be removed in 5.0.
 */
final class BCLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
    public function getLabel(string $label, string $context = '', string $type = ''): string
    {
        @trigger_error(\sprintf(
            'The "%s" class is deprecated since sonata-project/admin-bundle version 4.19 and will be'
            .' removed in 5.0 version.',
            self::class
        ), \E_USER_DEPRECATED);

        if ('breadcrumb' === $context) {
            return \sprintf('%s.%s_%s', $context, $type, strtolower($label));
        }

        return ucfirst(strtolower($label));
    }
}
