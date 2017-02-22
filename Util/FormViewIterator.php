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

    /**
     * @param FormView $formView
     */
    public function __construct(FormView $formView)
    {
        $this->iterator = $formView->getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return new self($this->current());
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        return count($this->current()->children) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->iterator->current();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->current()->vars['id'];
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }
}
