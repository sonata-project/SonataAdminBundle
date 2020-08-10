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

namespace Sonata\AdminBundle\Form\Extension\Field\Type;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormTypeExtensionInterface;

// NEXT_MAJOR: Remove the "else" part, copy all methods from BaseFormTypeFieldExtension in this class and
// extend from AbstractTypeExtension.
if (method_exists(FormTypeExtensionInterface::class, 'getExtendedTypes')) {
    /**
     * @final since sonata-project/admin-bundle 3.52
     *
     * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
     */
    class FormTypeFieldExtension extends BaseFormTypeFieldExtension
    {
        public static function getExtendedTypes(): iterable
        {
            return [FormType::class];
        }
    }
} else {
    /**
     * @final since sonata-project/admin-bundle 3.52
     *
     * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
     */
    class FormTypeFieldExtension extends BaseFormTypeFieldExtension
    {
        public static function getExtendedTypes()
        {
            return [FormType::class];
        }
    }
}
