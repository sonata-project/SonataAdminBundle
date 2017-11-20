The breadcrumbs builder
=======================

The ``sonata.admin.breadcrumbs_builder`` service is used in the layout of every
page to compute the underlying data for two breadcrumbs:

* one as text, appearing in the ``title`` tag of the document's ``head`` tag;
* the other as html, visible as an horizontal bar at the top of the page.

Getting the breadcrumbs for a given action of a given admin is done like this:

.. code-block:: php

   <?php
   $this->get('sonata.admin.breadcrumbs_builder')->getBreadcrumbs($admin, $action);

Configuration
-------------
.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        sonata_admin:
            breadcrumbs:
               # use this to change the default route used to generate the link to the parent object inside a breadcrumb, when in a child admin
               child_admin_route: edit