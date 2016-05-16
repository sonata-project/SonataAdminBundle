Extensions
==========

Admin extensions allow you to add or change features of one or more Admin instances. To create an extension your class
must implement the interface ``Sonata\AdminBundle\Admin\AdminExtensionInterface`` and be registered as a service. The
interface defines a number of functions which you can use to customize the edit form, list view, form validation,
alter newly created objects and other admin features.

.. code-block:: php

    use Sonata\AdminBundle\Admin\AbstractAdminExtension;
    use Sonata\AdminBundle\Form\FormMapper;

    class PublishStatusAdminExtension extends AbstractAdminExtension
    {
        public function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('status', 'choice', array(
                    'choices' => array(
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ),
                ))
            ;
        }
    }

Configuration
~~~~~~~~~~~~~

There are two ways to configure your extensions and connect them to an admin.

You can include this information in the service definition of your extension.
Add the tag *sonata.admin.extension* and use the *target* attribute to point to
the admin you want to modify. Please note you can specify as many tags you want.
Set the *global* attribute to *true* and the extension will be added to all admins.

.. configuration-block::

    .. code-block:: yaml

        services:
            app.publish.extension:
                class: AppBundle\Admin\Extension\PublishStatusAdminExtension
                tags:
                    - { name: sonata.admin.extension, target: app.admin.article }
                    - { name: sonata.admin.extension, target: app.admin.blog }

            app.order.extension:
                class: AppBundle\Admin\Extension\OrderAdminExtension
                tags:
                    - { name: sonata.admin.extension, global: true }

The second option is to add it to your config.yml file.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        sonata_admin:
            extensions:
                app.publish.extension:
                    admins:
                        - app.admin.article

Using the ``config.yml`` file has some advantages, it allows you to keep your configuration centralized and it provides some
extra options you can use to wire your extensions in a more dynamic way. This means you can change the behaviour of all
admins that manage a class of a specific type.

admins:
    specify one or more admin service ids to which the Extension should be added

excludes:
    specify one or more admin service ids to which the Extension should not be added (this will prevent it matching
    any of the other settings)

extends:
    specify one or more classes. If the managed class of an admin extends one of the specified classes the extension
    will be added to that admin.

implements:
    specify one or more interfaces. If the managed class of an admin implements one of the specified interfaces the
    extension will be added to that admin.

instanceof:
    specify one or more classes. If the managed class of an admin extends one of the specified classes or is an instance
    of that class the extension will be added to that admin.

uses:
    Requires PHP >= 5.4.0. Specify one or more traits. If the managed class of an admin uses one of the specified traits the extension will be
    added to that admin.


.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        sonata_admin:
            extensions:
                app.publish.extension:
                    admins:
                        - app.admin.article
                    implements:
                        - AppBundle\Publish\PublishStatusInterface
                    excludes:
                        - app.admin.blog
                        - app.admin.news
                    extends:
                        - AppBundle\Document\Blog
                    instanceof:
                        -  AppBundle\Document\Page
                    uses:
                        -  AppBundle\Trait\Timestampable
