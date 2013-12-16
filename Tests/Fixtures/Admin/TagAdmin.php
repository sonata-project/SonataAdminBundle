<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\Admin;

class TagAdmin extends Admin
{
    public function getParentAssociationMapping()
    {
        if ($this->getParent() instanceof PostAdmin) {
            return 'posts';
        }
    }
}

