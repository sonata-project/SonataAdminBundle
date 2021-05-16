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

namespace Sonata\AdminBundle\Util;

use Symfony\Component\Form\FormView;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class FormViewIterator implements \RecursiveIterator
{
    /**
     * @var \ArrayIterator<string, FormView>
     */
    private $iterator;

    public function __construct(FormView $formView)
    {
        $this->iterator = $formView->getIterator();
    }

    public function getChildren(): self
    {
        return new self($this->current());
    }

    public function hasChildren(): bool
    {
        return \count($this->current()->children) > 0;
    }

    public function current(): FormView
    {
        return $this->iterator->current();
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function key(): string
    {
        $current = $this->current();

        return $current->vars['id'];
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    public function rewind(): void
    {
        $this->iterator->rewind();
    }
}
