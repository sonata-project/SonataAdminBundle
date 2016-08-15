The Export action
=================

.. note::

    This document is a stub representing a new work in progress. If you're reading
    this you can help contribute, **no matter what your experience level with Sonata
    is**. Check out the `issues on GitHub`_ for more information about how to get involved.

This document will cover the Export action and related configuration options.

Basic configuration
-------------------

Translation
~~~~~~~~~~~

All field names are translated by default.
An internal mechanism checks if a field matching the translator strategy label exists in the current translation file
and will use the field name as a fallback.

.. note::

    **TODO**:
    * any global (yml) options that affect the export actions
    * how to disable (some of) the default formats
    * how to add new export formats
    * customising the templates used to render the output
    * customising the query used to fetch the results

.. _`issues on Github`: https://github.com/sonata-project/SonataAdminBundle/issues/1519
