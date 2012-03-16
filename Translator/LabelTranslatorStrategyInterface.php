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

interface LabelTranslatorStrategyInterface
{
    /**
     * @abstract
     * @param $label
     * @param $context
     * @param $type
     * @return string
     */
    function getLabel($label, $context = '', $type = '');
}
