Tags
======

sonata.admin.template_registry
------------------------------

This tag have few feature to allow manage for template. It is depends on with which interface it is used.

First case - use it directly on template registry:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml

        services:
            app.admin.post.template_registry:
                class: App\Templating\MyCustomTemplateRegistry
                tags:
                    - { name: sonata.admin.template_registry, template_name: 'show', template_path: 'PostAdmin/show.html.twig' }
                    - { name: sonata.admin.template_registry, template_name: 'edit', template_path: 'PostAdmin/edit.html.twig' }

    .. code-block:: xml

       <!-- config/services.xml -->

        <service id="app.admin.post.template_registry" class="App\Templating\MyCustomTemplateRegistry">
            <tag
                name="sonata.admin.template_registry"
                template_name="show"
                template_path="PostAdmin/show.html.twig"
                />
            <tag
                name="sonata.admin.template_registry"
                template_name="edit"
                template_path="PostAdmin/edit.html.twig"
                />
        </service>

Allow with interfaces:

    - `Sonata\AminBundle\Templating\TemplateRegistryInterface`
    - `Sonata\AminBundle\Templating\MutableTemplateRegistryInterface`

Result:

    - Templates will be set by order: `__construct()`, `setTemplate()` and `setTemplates()` methods call and by tag attributes.

Second case - use it on template registry aware:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml

        services:
            app.admin.post:
                class: App\Admin\PostAdmin
                arguments:
                    - ~
                    - App\Entity\Post
                    - ~
                tags:
                    - { name: sonata.admin, manager_type: orm, group: 'Content', label: 'Post' }
                    - { name: sonata.admin.template_registry, template_name: 'edit', template_path: 'PostAdmin/edit.html.twig' }

    .. code-block:: xml

       <!-- config/services.xml -->

        <service id="app.admin.post" class="App\Admin\PostAdmin">
            <tag name="sonata.admin" manager_type="orm" group="Content" label="Post"/>
            <argument/>
            <argument>App\Entity\Post</argument>
            <argument/>
            <tag
                name="sonata.admin.template_registry"
                template_name="edit"
                template_path="PostAdmin/edit.html.twig"
                />
        </service>

Allow with interfaces:

    - `Sonata\AminBundle\Templating\TemplateRegistryAwareInterface`
    - `Sonata\AminBundle\Templating\MutableTemplateRegistryAwareInterface`

Result:

    - Template registry will be set by `setTemplateRegistry` method call (optional)
    - Template registry will be auto generate when `setTemplateRegistry` is not call
    - Templates will be set/override

.. note::

    Using `sonata.admin.template_registry` tag with `setTemplateRegistry()` method call is not recommended. You CAN NOT override templates from `TemplateRegistry` and you SHOULD NOT override templates from `TemplateRegistryAware`.
