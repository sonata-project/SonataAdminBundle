<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Mapper\BaseGroupedMapper;

abstract class AbstractDummyGroupedMapper extends BaseGroupedMapper
{
    protected function getName()
    {
        return 'dummy';
    }
}
