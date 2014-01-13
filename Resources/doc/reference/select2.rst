Select2
=======

The admin comes with `select2 <http://ivaynberg.github.io/select2/>` integration
since version 2.2.6. Select2 is a jQuery based replacement for select boxes.
It supports searching, remote data sets, and infinite scrolling of results.

The select2 is enabled on all ``select`` form elements by default.

Disable select2
---------------

If you don't want to use select2 in your admin, you can disable it in config.yml.

.. configuration-block::

    .. code-block:: yaml

        sonata_admin:
            options:
                use_select2:    false # disable select2

.. note::
    If you disable select2, autocomplete form types will stop working.

Disable select2 on some form elements
-------------------------------------

To disable select2 on some ``select`` form element, set data attribute ``data-sonata-select2="false"`` to this form element.

.. code-block:: php

    ->add('category', 'sonata_type_model',
        array(
            'attr'=>array('data-sonata-select2'=>'false')
        )
    )

AllowClear
----------

Select2 parameter ``allowClear`` is handled automatically by admin. But if you want
to overload the default functionality, you can set data attribute ``data-sonata-select2-allow-clear="true"``
to enable ``allowClear`` or ``data-sonata-select2-allow-clear="false"`` to disable ``allowClear`` parameter.

.. code-block:: php

    ->add('category', 'sonata_type_model',
        array(
            'attr'=>array('data-sonata-select2-allow-clear'=>'false')
        )
    )
