<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;

class CommentAdmin extends Admin
{
    public function setClassnameLabel($label)
    {
        $this->classnameLabel = $label;
    }

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
    }

    public function setParentAssociationMapping($associationMapping)
    {
        $this->parentAssociationMapping = $associationMapping;
    }
}
