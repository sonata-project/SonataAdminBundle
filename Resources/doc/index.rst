Admin Bundle
============

**SonataAdminBundle is split into 5 bundles:**

* SonataAdminBundle: contains core libraries and services
* `SonataDoctrineORMAdminBundle <https://github.com/sonata-project/SonataDoctrineORMAdminBundle>`_: integrates Doctrine ORM project with the core admin bundle
* `SonataDoctrineMongoDBAdminBundle <https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle>`_: integrates MongoDB with the core admin bundle (early stage)
* `SonataDoctrinePhpcrAdminBundle <https://github.com/sonata-project/SonataDoctrinePhpcrAdminBundle>`_: integrates PHPCR with the core admin bundle (early stage)
* `SonataPropelAdminBundle <https://github.com/sonata-project/SonataPropelAdminBundle>`_: integrates Propel with the core admin bundle (early stage)

The demo website can be found at http://demo.sonata-project.org.

**Usage examples:**

* `SonataMediaBundle <https://github.com/sonata-project/SonataMediaBundle>`_: a media manager bundle
* `SonataNewsBundle <https://github.com/sonata-project/SonataNewsBundle>`_: a news/blog bundle
* `SonataPageBundle <https://github.com/sonata-project/SonataPageBundle>`_: a page (CMS like) bundle
* `SonataUserBundle <https://github.com/sonata-project/SonataUserBundle>`_: integration of FOSUserBundle and SonataAdminBundle

.. toctree::
    :caption: Getting Started
    :name: getting-started
    :maxdepth: 1
    :numbered:

    getting_started/installation
    getting_started/creating_an_admin
    getting_started/the_form_view
    getting_started/the_list_view

.. toctree::
   :caption: Reference Guide
   :name: reference-guide
   :maxdepth: 1
   :numbered:

   reference/installation
   reference/getting_started
   reference/configuration
   reference/architecture
   reference/dashboard
   reference/search
   reference/action_list
   reference/action_create_edit
   reference/action_show
   reference/action_delete
   reference/action_export
   reference/saving_hooks
   reference/form_types
   reference/form_help_message
   reference/field_types
   reference/batch_actions
   reference/console
   reference/troubleshooting
   reference/breadcrumbs

.. toctree::
   :caption: Advanced Options
   :name: advanced-options
   :maxdepth: 1
   :numbered:

   reference/routing
   reference/translation
   reference/conditional_validation
   reference/templates
   reference/security
   reference/extensions
   reference/events
   reference/advanced_configuration
   reference/annotations
   reference/preview_mode

.. toctree::
   :caption: Cookbook
   :name: cookbook
   :maxdepth: 1
   :numbered:

   cookbook/recipe_select2
   cookbook/recipe_knp_menu
   cookbook/recipe_file_uploads
   cookbook/recipe_image_previews
   cookbook/recipe_row_templates
   cookbook/recipe_sortable_listing
   cookbook/recipe_dynamic_form_modification
   cookbook/recipe_custom_action
   cookbook/recipe_customizing_a_mosaic_list
   cookbook/recipe_overwrite_admin_configuration
   cookbook/recipe_improve_performance_large_datasets
   cookbook/recipe_virtual_field
   cookbook/recipe_bootlint
   cookbook/recipe_lock_protection
   cookbook/recipe_sortable_sonata_type_model
   cookbook/recipe_delete_field_group
