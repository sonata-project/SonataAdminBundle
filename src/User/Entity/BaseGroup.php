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

namespace SensioLabs\AdminBundle\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use SensioLabs\AdminBundle\User\Model\Group;

#[ORM\MappedSuperclass]
abstract class BaseGroup extends Group
{
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    protected ?string $name = null;

    #[ORM\Column(type: 'json')]
    protected array $roles = [];
}
