Sortable behavior in admin listing
==================================

This is a full working example of how to implement a sortable feature in your Sonata admin listing

Background
----------

A sortable behavior is already available for one-to-many relationships (https://docs.sonata-project.org/projects/SonataDoctrineORMAdminBundle/en/4.x/reference/form_field_definition/#advanced-usage-one-to-many).
However there is no packaged solution to have some up and down arrows to sort
your records such as showed in the following screen

.. figure:: ../images/admin_sortable_listing.png
   :align: center
   :alt: Sortable listing
   :width: 700px

Pre-requisites
--------------

Configuration
^^^^^^^^^^^^^

- you already have SonataAdmin and DoctrineORM up and running
- you already have an Entity class for which you want to implement a sortable feature. For the purpose of the example we are going to call it ``Client``.
- you already have an Admin set up, in this example we will call it ``ClientAdmin``

Bundles
^^^^^^^
- install ``gedmo/doctrine-extensions`` bundle in your project (check ``stof/doctrine-extensions-bundle`` for easier integration in your project) and enable the sortable feature in your config
- install ``runroom-packages/sortable-behavior-bundle`` and enable it in ``config/bundles.php``

The recipe
----------

First of all we are going to add a position field in our ``Client`` entity::

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    private ?int $position = null;

Then we need to inject the Sortable listener.
If you only have the Gedmo bundle enabled, you only have to add the listener
to your ``services.yaml`` file and skip this step.

.. code-block:: yaml

    # config/services.yaml

    services:
        gedmo.listener.sortable:
            class: Gedmo\Sortable\SortableListener
            calls:
                - [setAnnotationReader, ['@annotation_reader']]
            tags:
                - { name: doctrine.event_subscriber, connection: default }

If you have the ``stof/doctrine-extensions-bundle``, you only need to enable the sortable
feature in your configuration such as

.. code-block:: yaml

    # config/packages/stof_doctrine_extensions.yaml

    stof_doctrine_extensions:
        orm:
            default:
                sortable: true

In our ``ClientAdmin`` we are going to add a custom action in the ``configureListFields`` method
and use the default twig template provided in the ``RunroomSortableBehaviorBundle``::

    $list
        ->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
            'actions' => [
                'move' => [
                    'template' => '@RunroomSortableBehavior/sort.html.twig'
                ],
            ]
        ]);

In order to add new routes for these actions and to apply right sorting use ``Runroom\SortableBehaviorBundle\Admin\SortableAdminTrait`` ::

    // src/Admin/ClientAdmin.php

    namespace App\Admin;

    use Runroom\SortableBehaviorBundle\Admin\SortableAdminTrait;
    use Sonata\AdminBundle\Admin\AbstractAdmin;

    class ClientAdmin extends AbstractAdmin
    {
        use SortableAdminTrait;
    }

Now you can update your ``services.yaml`` to use the handler provided by the ``RunroomSortableBehaviorBundle``

.. code-block:: yaml

    # config/services.yaml

    services:
        app.admin.client:
            class: App\Admin\ClientAdmin
            tags:
                - { name: sonata.admin, model_class: App\Entity\Client, controller: 'Runroom\SortableBehaviorBundle\Controller\SortableAdminController', manager_type: orm, label: 'Clients' }

Now we need to define sortable action::

    // src/Admin/ClientAdmin.php

    namespace App\Admin;

    use Runroom\SortableBehaviorBundle\Admin\SortableAdminTrait;
    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Datagrid\ListMapper;

    final class ClientAdmin extends AbstractAdmin
    {
        use SortableAdminTrait;

        protected function configureListFields(ListMapper $list): void
        {
            $list
                ->addIdentifier('name')
                ->add('enabled')
                ->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
                    'actions' => [
                        'move' => [
                            'template' => '@RunroomSortableBehavior/sort.html.twig'
                        ],
                    ],
                ])
            ;
        }
    }
