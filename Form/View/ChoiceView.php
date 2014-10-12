<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Sonata\AdminBundle\Form\View;

use Symfony\Component\Form\Extension\Core\View\ChoiceView as BaseView;

/**
 * Represents a choice in templates with specific attributes
 */
class ChoiceView extends BaseView
{
    /**
     * Some attributes, like routes
     *
     * @var array
     */
    public $attributes = array();

    /**
     * Creates a new ChoiceView.
     *
     * @param mixed  $data  The original choice.
     * @param string $value The view representation of the choice.
     * @param string $label The label displayed to humans.
     * @param array  $attributes Specific attributes of the choice
     */
    public function __construct($data, $value, $label, array $attributes = array())
    {
        parent::__construct($data, $value, $label);
        $this->attributes = $attributes;
    }
}
