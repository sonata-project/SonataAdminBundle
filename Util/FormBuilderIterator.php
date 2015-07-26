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
    protected static $reflection;

    protected $formBuilder;

    protected $keys = array();

    protected $prefix;

    protected $iterator;

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $formBuilder
     * @param bool                                         $prefix
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
     * @param \Symfony\Component\Form\FormBuilderInterface $formBuilder
     *
     * @return array
     */
    private static function getKeys(FormBuilderInterface $formBuilder)
    {
        if (!self::$reflection) {
            self::$reflection = new \ReflectionProperty(get_class($formBuilder), 'children');
            self::$reflection->setAccessible(true);
        }

        return array_keys(self::$reflection->getValue($formBuilder));
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->iterator->rewind();
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
    public function key()
    {
        $name = $this->iterator->current();

        return sprintf('%s_%s', $this->prefix, $name);
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
    public function current()
    {
        return $this->formBuilder->get($this->iterator->current());
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren()
    {
        return new self($this->formBuilder->get($this->iterator->current()), $this->current());
    }

    /**
     * {@inheritDoc}
     */
    public function hasChildren()
    {
        return count(self::getKeys($this->current())) > 0;
    }
}
