Sortable behavior in admin listing
==================================

This is a full working example of how to implement a sortable feature in your Sonata admin listing

Background
----------

A sortable behavior is already available for one-to-many relationships (http://sonata-project.org/bundles/doctrine-orm-admin/master/doc/reference/form_field_definition.html#advanced-usage-one-to-many). 
However there is no packaged solution to have some up and down arrows to sort your records such as showed in the following screen

list.jpg


Pre-requisites
--------------

- you already have SonataAdmin and DoctrineORM up and running
- you already have an Entity class for which you want to implement a sortable feature. For the purpose of the example we are going to call it ``Client``.
- you already have an Admin set up, in this example we will call it ``ClientAdmin``
- you already have gedmo/doctrine-extensions bundle in your project (check stof/doctrine-extensions or knplabs/doctrine-behaviors for easier integration in your project) with the sortable feature enabled

The recipe
----------

First of are going to add a position field in our ``Client`` entity.

.. code-block:: php

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    private $position;



In ``ClientAdmin`` our we are going to add in the ``configureListFields`` method a custom action

.. code-block:: php

	$listMapper
	->add('_action', 'actions', array(
                'actions' => array(
                    'move' => array('template' => 'AcmeDemoBundle:Admin:_sort.html.twig'),
                )
            ));



In order to add new routes for these actions we are also adding the following method

.. code-block:: php

	protected function configureRoutes(RouteCollection $collection)
	{
	collection->add('move', $this->getRouterIdParameter() . '/move/{position}');
	}


Moving up the position is not a problem since we now the top position will be 0. However moving down requires a bit more of logic since we need to know what is the last position. In order to get this done we are going to implement a service with a method returning the last position and another returning the position to move our object to.


.. code-block:: php

	<?php
	
	namespace Acme\DemoBundle\Services;
	
	use Doctrine\ORM\EntityManager;
	
	class PositionHandler
	{
	
	    /**
	     *
	     * @var EntityManager
	     */
	    protected $em;
	
	    public function __construct(EntityManager $entityManager)
	    {
	        $this->em = $entityManager;
	    }

	    public function getPosition($object, $position, $last_position)
	    {
	        switch ($position) {
	            case 'up' :
	                if ($object->getPosition() > 0) {
	                    $position = $object->getPosition() - 1;
	                }
	                break;
	
	            case 'down':
	                if ($object->getPosition() < $last_position) {
	                    $position = $object->getPosition() + 1;
	                }
	                break;
	
	            case 'top':
	                if ($object->getPosition() < $last_position) {
	                    $position = 0;
	                }
	                break;
	
	            case 'bottom':
	                if ($object->getPosition() < $last_position) {
	                    $position = $last_position;
	                }
	                break;
	        }
	
	
	        return $position;


	    public function getLastPosition()
	    {
	
	        $query = $this->em->createQuery('SELECT MAX(c.position) FROM AcmeDemoBundle:Client c');
	        $result = $query->getResult();
	        
	        if (array_key_exists(0, $result)) {
	            return $result[0][1];
	        }
	
	        return 0;
	    }
	}

We then need to declare thisservice

.. configuration-block::

    .. code-block:: yaml

	services:
	    acme_demo.client.position:
	        class: Acme\DemoBundle\Services\PositionHandler
	        arguments:
	            entityManager: "@doctrine.orm.entity_manager"



We can now create our controller to implement the action defined in our Admin class.

.. code-block:: php

	<?php
	
	namespace Acme\DemoBundle\Controller;
	
	use Sonata\AdminBundle\Controller\CRUDController;
	use Symfony\Component\HttpFoundation\RedirectResponse;
	
	class ClientAdminController extends CRUDController
	{
	   /**
	     * Move element
	     *
	     * @param integer $id
	     * @param string $position
	     */
	    public function moveAction($id, $position)
	    {
	        $object = $this->admin->getObject($id);
	
	        $position_service = $this->get('acme_demo.client.position');
	        $last_position = $position_service->getLastPosition();
	        $position = $position_service->getPosition($object, $position, $last_position);
	
	        $object->setPosition($position);
	        $this->admin->update($object);
	
	        $this->get('session')->setFlash('sonata_flash_info', 'Position updated');
	
	        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
	    }
	}


We now need to creat a handler for the new controller in ``admin.yml``

.. configuration-block::

    .. code-block:: yaml

	services:
	    acme.admin.client:
	        class: Acme\DemoBundle\Admin\ClientAdmin
	        tags:
	            - { name: sonata.admin, manager_type: orm, label: "Clients" }
	        arguments:
	            - ~
	            - Acme\DemoBundle\Entity\Client
	            - 'AcmeDemoBundle:ClientAdmin' # define the new controller via the third argument
	        calls:
	            - [ setTranslationDomain, [AcmeDemoBundle]]


Last tricky part, in order to get the last position available in our twig template we inject the service container in our admin class, define a public variable ``$last_position`` and retrieve the value from our service in the ``configureListFields`` method. We also define the sort by field to be position 

.. code-block:: php

   class ClientAdmin extends Admin
   {

    public $last_position = 0;

    private $container;

    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected $datagridValues = array(
        '_page' => 1,
        '_sort_order' => 'ASC',
        '_sort_by' => 'position',
    );


    protected function configureListFields(ListMapper $listMapper)
    {
        $position_service = $this->container->get('pix_equipe_medicale.medecin.position');
        $this->last_position = $position_service->getLastPosition();

        $listMapper
            ->addIdentifier('name')
            ->add('enabled')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'move' => array('template' => 'AcmeDemoBundle:Admin:_sort.html.twig'),
                )
            ));
    }

And in  the admin.yml add the following call

.. configuration-block::

    .. code-block:: yaml
    
	- [ setContainer, [ @service_container ] ]

Finally the twig files to display our up and down arrows in the listing

.. code-block:: jinja

	{# Acme/DemoBundle/Resources/views/Admin/_sort.html.twig #}
	{% if object.position < admin.last_position %}
	    <a class="movebottom_link" href="{{ admin.generateObjectUrl('move', object, {'position': 'bottom'}) }}" title="Move bottom">⇊</a>
	{% endif %}
	
	{% if object.position < admin.last_position %}
	    <a class="movedown_link" href="{{ admin.generateObjectUrl('move', object, {'position': 'down'}) }}" title="Move down">↓</a>
	{% endif %}
	
	{% if object.position > 0 %}
	    <a class="moveup_link" href="{{ admin.generateObjectUrl('move', object, {'position': 'up'}) }}" title="Move up">↑</a>
	{% endif %}
	
	{% if object.position > 0 %}
	    <a class="movetop_link" href="{{ admin.generateObjectUrl('move', object, {'position': 'top'}) }}" title="Move top">⇈</a>
	{% endif %}


Further work
------------

* handle ajax request
* create a separate bundle
* pull request for DoctrineOrm?
* interface for SonataAdmin?


Resources
---------

Adding a new action is explained in the Sonata documentation (http://sonata-project.org/bundles/admin/master/doc/reference/routing.html#create-a-route)

Controller code has been inspired from http://www.symfony.it/articoli/690/sonata-e-sortable/





