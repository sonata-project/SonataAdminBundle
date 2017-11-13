<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class CommentWithCustomRouteAdmin extends CommentAdmin
{
    protected $baseRoutePattern = 'comment-custom';
    protected $baseRouteName = 'comment_custom';
}
