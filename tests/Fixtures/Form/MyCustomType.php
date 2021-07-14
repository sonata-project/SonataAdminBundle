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

namespace Sonata\AdminBundle\Tests\Fixtures\Form;

use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\AbstractType;

class MyCustomType extends AbstractType
{
    public function getParent(): string
    {
        return ModelType::class;
    }
}
