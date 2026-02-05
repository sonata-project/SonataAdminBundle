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

namespace SensioLabs\AdminBundle\Tests\App\ORM\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Sub extends Base
{
    #[ORM\Column(options: ['default' => 'HELLO WORLD'])]
    public string $otherField = 'HELLO WORLD';
}
