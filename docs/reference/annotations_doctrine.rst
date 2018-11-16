Annotations using doctrine
==========================

All annotations require doctrine/annotations, it can be installed by composer:

.. code-block:: bash

    composer require doctrine/annotations


You need to enable the annotations afterwards.

.. configuration-block::

    .. code-block:: yaml

        sonata_admin:
            annotations:
                enable:     true
                directory: '%kernel.project_dir%/src/Admin'

Define Admins
^^^^^^^^^^^^^

All you have to do is include ``Sonata\AdminBundle\Annotation`` and define the values you need.

.. code-block:: php

    <?php

    namespace App\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Annotation as Sonata;

    /**
     * @Sonata\Admin(
     *   class="AcmeBundle\Entity\MyEntity",
     *   id="service id (generated per default)",
     *   managerType="doctrine_mongodb (orm per default)",
     *   baseControllerName="Sonata\AdminBundle\Controller\CRUDController",
     *   group="myGroup",
     *   label="myLabel",
     *   showInDashboard=true,
     *   translationDomain="AppBundle",
     *   pagerType="",
     *   persistFilters=false,
     *   icon="<i class='fa fa-folder'></i>",
     *   keepOpen=false,
     *   onTop=false
     * )
     */
    class MyAdmin extends AbstractAdmin
    {
        // ...
    }
