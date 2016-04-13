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

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class FormBuilderIterator.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class FormBuilderIterator extends \RecursiveArrayIterator
{
    /**
     * @var \ReflectionProperty
     */
    protected static $reflection;

    /**
     * @var FormBuilderInterface
     */
    protected $formBuilder;

    /**
     * @var array
     */
    protected $keys = array();

    /**
     * @var bool|string
     */
    protected $prefix;

    /**
     * @var \ArrayIterator
     */
    protected $iterator;

    /**
     * @param FormBuilderInterface $formBuilder
     * @param bool                 $prefix
     */
    public function __construct(FormBuilderInterface $formBuilder, $prefix = false)
    {
        $this->formBuilder = $formBuilder;
        $this->prefix      = $prefix ? $prefix : $formBuilder->getName();
        $this->iterator    = new \ArrayIterator(self::getKeys($formBuilder));
    }

    /**
     * @static
     *
     * @param FormBuilderInterface $formBuilder
     *
     * @return array
     */
    private static function getKeys(FormBuilderInterface $formBuilder)
    {
        return array_keys($formBuilder->all());
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->iterator->rewind();
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
    public function key()
    {
        $name = $this->iterator->current();

        return sprintf('%s_%s', $this->prefix, $name);
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
    public function current()
    {
        return $this->formBuilder->get($this->iterator->current());
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return new self($this->formBuilder->get($this->iterator->current()), $this->current());
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        return count(self::getKeys($this->current())) > 0;
    }
}
