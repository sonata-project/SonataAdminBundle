<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bundle\Sonata\BaseApplicationBundle\Form;

use Symfony\Component\Form\RecursiveFieldIterator as BaseRecursiveFieldIterator;
use Symfony\Component\Form\FieldGroupInterface;

class RecursiveFieldIterator extends BaseRecursiveFieldIterator
{

    public function hasChildren()
    {

        return $this->current() instanceof FieldGroupInterface;
    }
}
