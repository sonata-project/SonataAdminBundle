<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Util;

use Symfony\Component\Form\FormView;

class FormViewIterator implements \RecursiveIterator
{
    protected $formView;

    public function __construct(FormView $formView)
    {
        $this->iterator = $formView->getIterator();
    }

    public function getChildren()
    {
        return new FormViewIterator($this->current());
    }

    public function hasChildren()
    {
        return $this->current()->hasChildren();
    }

    public function current()
    {
        return $this->iterator->current();
    }

    public function next()
    {
        return $this->iterator->next();
    }

    public function key()
    {
        return $this->current()->get('id');
    }

    public function valid()
    {
        return $this->iterator->valid();
    }

    public function rewind()
    {
        return $this->iterator->rewind();
    }
}