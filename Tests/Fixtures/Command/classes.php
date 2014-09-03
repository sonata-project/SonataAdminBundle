<?php
namespace Sonata\AdminBundle\Tests\Fixtures\Controller
{
abstract class AbstractFooAdminController
{
public function bazAction()
{
}
}
}
namespace Sonata\AdminBundle\Tests\Fixtures\Controller
{
use Sonata\AdminBundle\Tests\Fixtures\Controller\AbstractFooAdminController;
class FooAdminController extends AbstractFooAdminController
{
public function fooAction($baz)
{
}
}
}
namespace Sonata\AdminBundle\Tests\Fixtures\Controller
{
class BarAdminController
{
public function barAction()
{
}
}
}