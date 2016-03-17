Annotations
===========

All annotations require jms/di-extra-bundle, it can easily be installed by composer:

.. code-block:: bash

    composer require jms/di-extra-bundle


if you want to know more: http://jmsyst.com/bundles/JMSDiExtraBundle


Define Admins
^^^^^^^^^^^^^

All you have to do is include Sonata\AdminBundleAnnotations and define the values you need.

.. code-block:: php

    <?php

    namespace AcmeBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Annotation as Sonata;

    /**
     * @Sonata\Admin(
     *   class="AcmeBundle\Entity\MyEntity"
     * )
     */
    class MyAdmin extends Admin
    {
    }


.. note::

    If you need to define custom controllers you can also use jms/di-extra-bundle by using
    the DI\Service annotation.
