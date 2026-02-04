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

namespace SensioLabs\AdminBundle\Tests\App\FieldDescription;

use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;

final class FieldDescriptionFactory implements FieldDescriptionFactoryInterface
{
    public function create(string $class, string $name, array $options = []): FieldDescriptionInterface
    {
        return new FieldDescription($name, $options);
    }
}
