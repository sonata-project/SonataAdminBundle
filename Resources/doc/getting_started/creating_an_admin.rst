Creating an Admin
=================

You've been able to get the admin interface working in :doc:`the previous
chapter <installation>`. In this tutorial, you'll learn how to tell SonataAdmin
how an admin can manage your models.

Step 0: Create a Model
----------------------

For the rest of the tutorial, you'll need some sort of model. In this tutorial,
two very simple ``Post`` and ``Tag`` entities will be used. Generate them by
using these commands:

.. code-block:: bash

    $ php bin/console doctrine:generate:entity --entity="AppBundle:Category" --fields="name:string(255)" --no-interaction
    $ php bin/console doctrine:generate:entity --entity="AppBundle:BlogPost" --fields="title:string(255) body:text draft:boolean" --no-interaction

After this, you'll need to tweak the entities a bit:

.. code-block:: php

    // src/AppBundle/Entity/BlogPost.php

    // ...
    class BlogPost
    {
        // ...

        /**
         * @ORM\ManyToOne(targetEntity="Category", inversedBy="blogPosts")
         */
        private $category;

        public function setCategory(Category $category)
        {
            $this->category = $category;
        }

        public function getCategory()
        {
            return $this->category;
        }

        // ...
    }

Set the default value to ``false``.

.. code-block:: php

    // src/AppBundle/Entity/BlogPost.php

    // ...
    class BlogPost
    {
        // ...

        /**
         * @var bool
         *
         * @ORM\Column(name="draft", type="boolean")
         */
        private $draft = false;

        // ...
    }

.. code-block:: php


    // src/AppBundle/Entity/Category.php

    // ...
    use Doctrine\Common\Collections\ArrayCollection;
    // ...

    class Category
    {
        // ...

        /**
        * @ORM\OneToMany(targetEntity="BlogPost", mappedBy="category")
        */
        private $blogPosts;

        public function __construct()
        {
            $this->blogPosts = new ArrayCollection();
        }

        public function getBlogPosts()
        {
            return $this->blogPosts;
        }

        // ...
    }

After this, create the schema for these entities:

.. code-block:: bash

    $ php bin/console doctrine:schema:create

.. note::

    This article assumes you have basic knowledge of the Doctrine2 ORM and
    you've set up a database correctly.

Step 1: Create an Admin Class
-----------------------------

SonataAdminBundle helps you manage your data using a graphical interface that
will let you create, update or search your model instances. The bundle relies
on Admin classes to know which models will be managed and how these actions
will look like.

An Admin class decides which fields to show on a listing, which fields are used
to find entries and how the create form will look like. Each model will have
its own Admin class.

Knowing this, let's create an Admin class for the ``Category`` entity. The
easiest way to do this is by extending ``Sonata\AdminBundle\Admin\AbstractAdmin``.

.. code-block:: php

    // src/AppBundle/Admin/CategoryAdmin.php
    namespace AppBundle\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Form\FormMapper;

    class CategoryAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper->add('name', 'text');
        }

        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            $datagridMapper->add('name');
        }

        protected function configureListFields(ListMapper $listMapper)
        {
            $listMapper->addIdentifier('name');
        }
    }

So, what does this code do?

* **Line 11-14**: These lines configure which fields are displayed on the edit
  and create actions. The ``FormMapper`` behaves similar to the ``FormBuilder``
  of the Symfony Form component;
* **Line 16-19**: This method configures the filters, used to filter and sort
  the list of models;
* **Line 21-24**: Here you specify which fields are shown when all models are
  listed (the ``addIdentifier()`` method means that this field will link to the
  show/edit page of this particular model).

This is the most basic example of the Admin class. You can configure a lot more
with the Admin class. This will be covered by other, more advanced, articles.

Step 3: Register the Admin class
--------------------------------

You've now created an Admin class, but there is currently no way for the
SonataAdminBundle to know that this Admin class exists. To tell the
SonataAdminBundle of the existence of this Admin class, you have to create a
service and tag it with the ``sonata.admin`` tag:

.. code-block:: yaml

    # app/config/services.yml

    services:
        # ...
        admin.category:
            class: AppBundle\Admin\CategoryAdmin
            arguments: [~, AppBundle\Entity\Category, ~]
            tags:
                - { name: sonata.admin, manager_type: orm, label: Category }

The constructor of the base Admin class has many arguments. SonataAdminBundle
provides a compiler pass which takes care of configuring it correctly for you.
You can often tweak things using tag attributes. The code shown here is the
shortest code needed to get it working.

Step 4: Register SonataAdmin custom Routes
------------------------------------------

SonataAdminBundle generates routes for the Admin classes on the fly. To load these
routes, you have to make sure the routing loader of the SonataAdminBundle is executed:

.. code-block:: yaml

    # app/config/routing.yml

    # ...
    _sonata_admin:
        resource: .
        type: sonata_admin
        prefix: /admin

View the Category Admin Interface
---------------------------------

Now you've created the admin class for your category, you probably want to know
how this looks like in the admin interface. Well, let's find out by going to
http://localhost:8000/admin

.. image:: ../images/getting_started_category_dashboard.png

Feel free to play around and add some categories, like "Symfony" and "Sonata
Project". In the next chapters, you'll create an admin for the ``BlogPost``
entity and learn more about this class.

.. tip::

    If you're not seeing the nice labels, but instead something like
    "link_add", you should make sure that you've `enabled the translator`_.

.. _`enabled the translator`: http://symfony.com/doc/current/book/translation.html#configuration
