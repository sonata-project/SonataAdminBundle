Extensions
==========

Admin extensions allow you to add or change features of one or more Admin
instances. To create an extension your class must implement one or more interfaces
in the ``Sonata\AdminBundle\Admin\Extension`` namespace and be registered as a service.
The interface defines a number of functions which you can use to customize the
edit form, list view, form validation, alter newly created objects and other admin
features.

.. note::
    This article assumes you are using Symfony 4. Using Symfony 2.8 or 3
    will require to slightly modify some namespaces and paths when creating
    entities and admins.

.. code-block:: php

    use Sonata\AdminBundle\Admin\Extension\ConfigureFormFieldsInterface;
    use Sonata\AdminBundle\Form\FormMapper;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

    class PublishStatusAdminExtension implements ConfigureFormFieldsInterface
    {
        public function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ],
                ])
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
The *priority* attribute is *0* by default and can be a positive or negative integer.
The higher the priority, the earlier it's executed.

.. configuration-block::

    .. code-block:: yaml

        services:
            app.publish.extension:
                class: App\Admin\Extension\PublishStatusAdminExtension
                tags:
                    - { name: sonata.admin.extension, target: app.admin.article }
                    - { name: sonata.admin.extension, target: app.admin.blog }

            app.order.extension:
                class: App\Admin\Extension\OrderAdminExtension
                tags:
                    - { name: sonata.admin.extension, global: true }

            app.important.extension:
                class: App\Admin\Extension\ImportantAdminExtension
                tags:
                    - { name: sonata.admin.extension, priority: 5 }

The second option is to add it to your config.yml file.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/sonata_admin.yaml

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

priority:
    Can be a positive or negative integer. The higher the priority, the earlier it’s executed.


.. configuration-block::

    .. code-block:: yaml

        # config/packages/sonata_admin.yaml

        sonata_admin:
            extensions:
                app.publish.extension:
                    admins:
                        - app.admin.article
                    implements:
                        - App\Publish\PublishStatusInterface
                    excludes:
                        - app.admin.blog
                        - app.admin.news
                    extends:
                        - App\Document\Blog
                    instanceof:
                        -  App\Document\Page
                    uses:
                        -  App\Trait\Timestampable
