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

use Symfony\Component\Form\FormBuilder;

class FormBuilderIterator extends \RecursiveArrayIterator
{
    static protected $reflection;

    protected $formBuilder;

    protected $keys = array();

    protected $prefix;

    public function __construct(FormBuilder $formBuilder, $prefix = false)
    {
        $this->formBuilder = $formBuilder;
        $this->prefix      = $prefix ? $prefix : $formBuilder->getName();
        $this->iterator    = new \ArrayIterator(self::getKeys($formBuilder));
    }

    private static function getKeys(FormBuilder $formBuilder)
    {
        if (!self::$reflection) {
            self::$reflection = new \ReflectionProperty(get_class($formBuilder), 'children');
            self::$reflection->setAccessible(true);
        }

        return array_keys(self::$reflection->getValue($formBuilder));
    }

    public function rewind()
    {
        return $this->iterator->rewind();
    }

    public function valid()
    {
        return $this->iterator->valid();
    }

    public function key()
    {
        $name = $this->iterator->current();

        return sprintf('%s_%s', $this->prefix, $name);
    }

    public function next()
    {
        return $this->iterator->next();
    }

    public function current()
    {
        return $this->formBuilder->get($this->iterator->current());
    }

    public function getChildren()
    {
        return new self($this->formBuilder->get($this->iterator->current()), $this->current());
    }

    public function hasChildren()
    {
        return count(self::getKeys($this->current())) > 0;
    }
}