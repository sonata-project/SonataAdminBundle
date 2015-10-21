Lock Protection
===============

Lock protection will prevent data corruption when multiple users edit an object at the same time.

Example
-------

1) Alice starts to edit the object
2) Bob starts to edit the object
3) Alice submits the form
4) Bob submits the form

In this case, a message will tell Bob that someone else has edited the object,
and that he must reload the page and apply the changes again.

Enable Lock Protection
----------------------

By default, lock protection is disabled.
You can enable it in your ``sonata_admin`` configuration :

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        sonata_admin:
            options:
                lock_protection: true
                
You must also configure each entity that you want to support by adding a field called ``$version`` on which the Doctrine ``Version`` feature is activated.

Using Annotations:

.. code-block:: php

    <?php
    // src/AppBundle/Entity/Car.php
    namespace AppBundle\Entity\Car;
    
    use Doctrine\ORM\Mapping as ORM;

    class Car
    {
        // ...
        
        /**
         * @ORM\Column(type="integer")
         * @ORM\Version
         */
        protected $version;
        
        // ...
    }

Using XML:

.. code-block:: xml

    <?xml version="1.0" encoding="utf-8"?>
    <!-- src/AppBundle/Resources/orm/Car.orm.xml -->
    <doctrine-mapping>
        <entity name="AppBundle\Entity\Car">
            <!-- ... -->
    
            <field name="version" type="integer" version="true" />
    
            <!-- ... -->
        </entity>
    </doctrine-mapping>
    

For more information about this visit the `Doctrine docs <http://doctrine-orm.readthedocs.org/en/latest/reference/transactions-and-concurrency.html?highlight=optimistic#optimistic-locking>`_

.. note::

    If the object model manager does not support object locking,
    the lock protection will not be triggered for the object.
    Currently, only the ``SonataDoctrineORMAdminBundle`` supports it.
