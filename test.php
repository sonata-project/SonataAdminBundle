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

require 'vendor/autoload.php';

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\CoreBundle\Validator\ErrorElement;

class A extends AbstractAdmin
{
    public function validate(ErrorElement $errorElement, $object)
    {
    }
}
