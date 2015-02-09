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

use Sonata\CoreBundle\Validator\Constraints\InlineConstraint as BaseInlineConstraint;

/**
 * @deprecated
 */
class InlineConstraint extends BaseInlineConstraint
{
    /**
     * {@inheritDoc}
     */
    public function validatedBy()
    {
        return 'sonata.admin.validator.inline';
    }
}
