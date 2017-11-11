<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

class PostWithCustomRouteAdmin extends PostAdmin
{
    protected $baseRoutePattern = '/post-custom';
    protected $baseRouteName = 'post_custom';
}
