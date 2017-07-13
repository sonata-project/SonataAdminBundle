Annotations
===========

All annotations require jms/di-extra-bundle, it can easily be installed by composer:

.. code-block:: bash

    composer require jms/di-extra-bundle


if you want to know more: http://jmsyst.com/bundles/JMSDiExtraBundle

The annotations get registered with JMSDiExtraBundle automatically if it is installed.
If you need to disable this for some reason, you can do this via the configuration:

.. configuration-block::

    .. code-block:: yaml

        sonata_admin:
            options:
                enable_jms_di_extra_autoregistration: false

.. note::

    Starting with version 4.0, SonataAdminBundle will no longer register
    annotations with JMSDiExtraBundle automatically. Please add the following to
    your config.yml to register the annotations yourself:

    .. code-block:: yaml

        jms_di_extra:
            annotation_patterns:
                - JMS\DiExtraBundle\Annotation
                - Sonata\AdminBundle\Annotation


Define Admins
^^^^^^^^^^^^^

All you have to do is include ``Sonata\AdminBundle\Annotation`` and define the values you need.

.. code-block:: php

    <?php

    namespace AcmeBundle\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Annotation as Sonata;

    /**
     * @Sonata\Admin(
     *   class="AcmeBundle\Entity\MyEntity",
     *   id="service id (generated per default)",
     *   managerType="doctrine_mongodb (orm per default)",
     *   baseControllerName="SonataAdminBundle:CRUD",
     *   group="myGroup",
     *   label="myLabel",
     *   showInDashboard=true,
     *   translationDomain="OMG",
     *   pagerType="",
     *   persistFilters="",
     *   icon="<i class='fa fa-folder'></i>",
     *   keepOpen=false,
     *   onTop=false
     * )
     */
    class MyAdmin extends AbstractAdmin
    {
    }


.. note::

    If you need to define custom controllers you can also use jms/di-extra-bundle by using
    the DI\Service annotation.
