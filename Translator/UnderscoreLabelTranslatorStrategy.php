<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Translator;

class UnderscoreLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
    /**
     * @param string $label
     * @return string
     */
    public function getLabel($label)
    {
        return sprintf('label_%s', strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $label)));
    }
}