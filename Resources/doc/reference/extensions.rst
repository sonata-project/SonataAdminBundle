Extensions
=======

You can dynamically manage Admin Extensions using the SonataAdminBundle's configuration. Each child of the 'extensions' node should be the service id of an AdminExtension.
For each extension you have the following wiring options:

### admins
specify one or more Admin service id's to which the Extension should be added

### excludes
specify one or more Admin service id's to which the Extension should not be added

### implements
specify one or more interfaces. If the managed class of an admin implements one of the specified interfaces the extension will be added to that admin.

### extends
specify one or more classes. If the managed class of an admin extends one of the specified classes the extension will be added to that admin.

### instanceof
specify one or more classes. If the managed class of an admin extends one of the specified classes or is an instance of that class the extension will be added to that admin.


.. code-block:: yaml

    # app/config/config.yml
        sonata_admin:
            extensions:
                acme.demo.content.publishable.extension:
                    admins:
                        - acme.demo.post.admin
                        - acme.demo.news.admin
                    implements:
                        - Acme\Demo\Content\PublishableInterface
                    excludes:
                        - acme.demo.blog.admin
                    extends:
                        - Acme\Demo\Document\Page
                    instanceof:
                        -  Acme\Demo\Document\Blog




