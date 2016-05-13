<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

class TagAdmin extends AbstractAdmin
{
    public function getParentAssociationMapping()
    {
        if ($this->getParent() instanceof PostAdmin) {
            return 'posts';
        }
    }
}
