Lock protection
==========================

Lock protection will prevent data corruption when multiple users edit an object at the same time.

Example
^^^^^^^

1) Alice starts to edit the object
2) Bob starts to edit the object
3) Alice submits the form
4) Bob submits the form

In this case, a message will tell Bob that someone else has edited the object,
and that he must reload the page and apply the changes again.

Enable lock protection
----------------------

By default, lock protection is disabled. You can enable it in your ``sonata_admin`` configuration :

.. configuration-block::

    .. code-block:: yaml

        sonata_admin:
            options:
                lock_protection: true

.. note::
    If the object model manager does not supports object locking,
    the lock protection will not be triggered for the object.
    Currently, only the ``SonataDoctrineORMAdminBundle`` supports it.
