<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Fixtures\Form;

use SensioLabs\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\AbstractType;

final class MyCustomType extends AbstractType
{
    public function getParent(): string
    {
        return ModelType::class;
    }
}
