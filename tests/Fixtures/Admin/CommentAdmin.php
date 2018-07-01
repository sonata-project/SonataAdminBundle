<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class CommentAdmin extends AbstractAdmin
{
    public function setClassnameLabel($label)
    {
        $this->classnameLabel = $label;
    }

    public function configureRoutes(RouteCollection $collection): void
    {
        $collection->remove('edit');
    }

    public function setParentAssociationMapping($associationMapping): void
    {
        $this->parentAssociationMapping = $associationMapping;
    }
}
