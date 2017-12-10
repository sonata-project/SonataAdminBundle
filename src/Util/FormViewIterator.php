<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Util;

use Symfony\Component\Form\FormView;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class FormViewIterator implements \RecursiveIterator
{
    /**
     * @var \ArrayIterator
     */
    protected $iterator;

    public function __construct(FormView $formView)
    {
        $this->iterator = $formView->getIterator();
    }

    public function getChildren()
    {
        return new self($this->current());
    }

    public function hasChildren()
    {
        return count($this->current()->children) > 0;
    }

    public function current()
    {
        return $this->iterator->current();
    }

    public function next()
    {
        $this->iterator->next();
    }

    public function key()
    {
        return $this->current()->vars['id'];
    }

    public function valid()
    {
        return $this->iterator->valid();
    }

    public function rewind()
    {
        $this->iterator->rewind();
    }
}
