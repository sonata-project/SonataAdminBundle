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

    /**
     * @param \Symfony\Component\Form\FormView $formView
     */
    public function __construct(FormView $formView)
    {
        $this->iterator = $formView->getIterator();
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren()
    {
        return new FormViewIterator($this->current());
    }

    /**
     * {@inheritDoc}
     */
    public function hasChildren()
    {
        return count($this->current()->children) > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return $this->iterator->current();
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->current()->vars['id'];
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }
}
