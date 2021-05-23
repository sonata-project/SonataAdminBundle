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

namespace Sonata\AdminBundle\Tests\Fixtures\Mapper;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;

abstract class AbstractDummyGroupedMapper extends BaseGroupedMapper
{
    /**
     * @var AdminInterface<object>
     */
    private $admin;

    public function __construct(AdminInterface $admin)
    {
        $this->admin = $admin;
    }

    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    protected function getName(): string
    {
        return 'dummy';
    }
}
