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

namespace SensioLabs\AdminBundle\Tests\Fixtures\Mapper;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Mapper\BaseGroupedMapper;

/**
 * @phpstan-extends BaseGroupedMapper<object>
 */
abstract class AbstractDummyGroupedMapper extends BaseGroupedMapper
{
    /**
     * @param AdminInterface<object> $admin
     */
    public function __construct(
        private AdminInterface $admin,
    ) {
    }

    public function add(string $fieldName, ?string $name = null): self
    {
        $this->addFieldToCurrentGroup($fieldName, $name);

        return $this;
    }

    /**
     * @return AdminInterface<object>
     */
    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    protected function getName(): string
    {
        return 'dummy';
    }
}
