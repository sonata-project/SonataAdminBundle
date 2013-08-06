SonataAdminBundle - The missing Symfony2 Admin Generator
========================================================

[![Build Status](https://secure.travis-ci.org/sonata-project/SonataAdminBundle.png?branch=master)](http://travis-ci.org/sonata-project/SonataAdminBundle)

The online documentation of the bundle is in http://sonata-project.org/bundles/admin

The demo website can be found in http://demo.sonata-project.org/admin/dashboard (admin as user and password)

For contribution to the documentation you can find it on [Resources/doc](https://github.com/sonata-project/SonataAdminBundle/tree/master/Resources/doc).

**Warning**: documentation files are not rendering correctly in Github (reStructuredText format)
and some content might be broken or hidden, make sure to read raw files.

**Warning**: The bundle has been split into 4 bundles :

* SonataAdminBundle : the current one, contains core libraries and services
* [SonataDoctrineORMAdminBundle](https://github.com/sonata-project/SonataDoctrineORMAdminBundle) 
: Integrates the admin bundle into with the Doctrine ORM project
* [SonataDoctrineMongoDBAdminBundle](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle) 
: Integrates the admin bundle into with MongoDB (early stage)
* [SonataDoctrinePhpcrAdminBundle](https://github.com/sonata-project/SonataDoctrinePhpcrAdminBundle) 
: Integrates the admin bundle into with PHPCR (early stage)

**Google Groups**: For questions and proposals you can post on this google groups

* [Sonata Users](https://groups.google.com/group/sonata-users): Only for user questions
* [Sonata Devs](https://groups.google.com/group/sonata-devs): Only for devs

Features
--------

  - Dashboard

  - List

    - Automatic sort
    - Link to associated admin (Post => User)
    - Custom templates
    - Row Action : edit, view, ...
    - Batch Action
    - Clever row visualisation : boolean values are represented with 'check picture'
    - Filter
    - Pagination

  - Edit/Create

    - Inline edition
    - Association management (create related model with + icon)
    - Group fields
    - Sortable option
    - Modal window to select model (when the list can be important)
    - Dynamic form on [one|many]-to-many association (add new element)

  - Templating

    - base templates (field, list, filter) can be overwritten
    - layout templates can be defined into the Service Container

  - Others

    - Nested Admin, ie /news/post/5/comment/list : filter and create comments only for the post with id=5
    - Contextual Breadcrumb
    - persistent parameters across an Admin
    - side menu option
    - Translated into 24 languages : BG, CA, CS, DE, EN, ES, EU, FA, FR, HR, HU, IT, JA, LB, NL, PL, PT, PT_BR, RO, RU, SK, SL, UK and zh_CN.
    - Built to be extended
    - Explain command line utility


Usage examples
--------------

 - [SonataMediaBundle](https://github.com/sonata-project/SonataMediaBundle) : a media manager bundle
 - [SonataNewsBundle](https://github.com/sonata-project/SonataNewsBundle) : a news/blog bundle
 - [SonataPageBundle](https://github.com/sonata-project/SonataPageBundle) : a page (CMS like) bundle
 - [SonataUserBundle](https://github.com/sonata-project/SonataUserBundle) : integration of FOSUserBundle and SonataAdminBundle

TODO
----

  - create the ODM version
  - save filter criteria
