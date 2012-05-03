<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class InlineConstraint extends Constraint
{
    protected $service;

    protected $method;

    /**
     * {@inheritDoc}
     */
    public function validatedBy()
    {
        return 'sonata.admin.validator.inline';
    }

    /**
     * @return bool
     */
    public function isClosure()
    {
        return $this->method instanceof \Closure;
    }

    /**
     * @return mixed
     */
    public function getClosure()
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredOptions()
    {
        return array(
            'service',
            'method'
        );
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }
}
