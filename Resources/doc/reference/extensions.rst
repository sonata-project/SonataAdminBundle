Extensions
==========

Admin extensions allow you to add or change features of one or more Admin instances. To create an extension your class
must implement the interface ``Sonata\AdminBundle\Admin\AdminExtensionInterface`` and be registered as a service. The
interface defines a number of functions which you can use to customize the edit form, list view, form validation and
other admin features.

.. code-block:: php

    use Sonata\AdminBundle\Admin\AdminExtension;
    use Sonata\AdminBundle\Form\FormMapper;

    class PublishStatusAdminExtension extends AdminExtension
    {
        public function configureFormFields(FormMapper $formMapper)
        {
            $formMapper->add('status', 'choice', array(
                'choices' => array(
                    'draft' => 'Draft',
                    'published' => 'Published',
                ),
            ));
        }
    }

Configuration
~~~~~~~~~~~~~

There are two ways to configure your extensions and connect them to an admin.

You can include this information in the service definition of your extension.
Add the tag *sonata.admin.extension* and use the *target* attribute to point to the admin you want to modify.

.. code-block:: yaml

    services:
        acme.demo.publish.extension:
            class: Acme\Demo\BlogBundle\Admin\Extension\PublishStatusAdminExtension
            tags:
                - { name: sonata.admin.extension, target: acme.demo.admin.article }

The second option is to add it to your config.yml file.

.. code-block:: yaml

    # app/config/config.yml
        sonata_admin:
            extensions:
                acme.demo.publish.extension:
                    admins:
                        - acme.demo.admin.article

Using the config.yml file has some advantages, it allows you to keep your configuration centralized and it provides some
extra options you can use to wire your extensions in a more dynamic way. This means you can change the behaviour of all
admins that manage a class of a specific type.

| **admins**
| specify one or more admin service id's to which the Extension should be added

| **excludes**
| specify one or more admin service id's to which the Extension should not be added

| **implements**
| specify one or more interfaces. If the managed class of an admin implements one of the specified interfaces the
| extension will be added to that admin.

| **extends**
| specify one or more classes. If the managed class of an admin extends one of the specified classes the extension
| will be added to that admin.

| **instanceof**
| specify one or more classes. If the managed class of an admin extends one of the specified classes or is an instance
| of that class the extension will be added to that admin.

.. code-block:: yaml

    # app/config/config.yml
        sonata_admin:
            extensions:
                acme.demo.publish.extension:
                    admins:
                        - acme.demo.admin.article
                    implements:
                        - Acme\Demo\Publish\PublishStatusInterface
                    excludes:
                        - acme.demo.admin.blog
                        - acme.demo.admin.news
                    extends:
                        - Acme\Demo\Document\Blog
                    instanceof:
                        -  Acme\Demo\Document\Page

