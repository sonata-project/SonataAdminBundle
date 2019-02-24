Auto Configuring Admin Classes
==============================

If you have a lot of admin classes and don't want to write all the service
definitions or if maybe you don't like writing configuration and want
everything set up for you, this bundle will allow you not to do that.

Download the SonataAutoConfigureBundle
--------------------------------------

.. code-block:: bash

    composer require kunicmarko/sonata-auto-configure-bundle

How to use
----------

The only thing you need to do is add the configuration for this bundle:

.. code-block:: yaml

    # config/packages/sonata_auto_configure.yaml

    sonata_auto_configure:
        admin:
            suffix: Admin
            manager_type: orm
        entity:
            namespaces:
                - { namespace: App\Entity, manager_type: orm }
        controller:
            suffix: Controller
            namespaces:
                - App\Controller\Admin

.. note::

    Be sure that the admin directory is included in
    auto discovery and that autoconfigure is enabled.

This configuration means, find all admin classes,
remove the ``Admin`` suffix and try to find an entity with the
same name in ``App\Entity`` namespace and add the ``orm`` ``manager_type``.
After, try to find the controllers in ``App\Controller\Admin`` but
replace the ``Admin`` suffix for ``Controller``.

If you are interested in more details on how this bundle works, read it `here`_.

Annotations
-----------

Annotations have a higher priority than bundle guesses, so if you are not happy
with something, you can add the annotation to your admin class::

    namespace App\Admin;

    use App\Controller\Admin\CategoryController;
    use App\Entity\Category;
    use KunicMarko\SonataAutoConfigureBundle\Annotation as Sonata;
    use Sonata\AdminBundle\Admin\AbstractAdmin;

    /**
     * @Sonata\AdminOptions(
     *     label="Category",
     *     managerType="orm",
     *     group="Category",
     *     showInDashboard=true,
     *     keepOpen=true,
     *     onTop=true,
     *     icon="<i class='fa fa-user'></i>",
     *     labelTranslatorStrategy="sonata.admin.label.strategy.native",
     *     labelCatalogue="App",
     *     pagerType="simple",
     *     controller=CategoryController::class,
     *     entity=Category::class,
     *     adminCode="admin_code",
     *     autowireEntity=true,
     * )
     */
    final class CategoryAdmin extends AbstractAdmin
    {
    }

.. _`here`: https://github.com/kunicmarko20/SonataAutoConfigureBundle#how-does-it-work
