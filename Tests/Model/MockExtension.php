<?php
/**
 * (c) Netvlies Internetdiensten
 *
 * @author Sjoerd Peters <speters@netvlies.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\AdminBundle\Tests\Model;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\AdminExtensionInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;

class MockExtension implements AdminExtensionInterface {
    public function configureFormFields(FormMapper $form){}
    public function configureListFields(ListMapper $list){}
    public function configureDatagridFilters(DatagridMapper $filter){}
    public function configureShowFields(ShowMapper $filter){}
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection){}
    public function configureSideMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null){}
    public function validate(AdminInterface $admin, ErrorElement $errorElement, $object){}
    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list'){}
}
