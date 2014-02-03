Bootstrap x-editable
====================

The admin comes with `Bootstrap x-editable <http://vitalets.github.io/x-editable/>` integration
since version 2.2.6. With this js plugin it's possible to edit field values in list view
on the fly.

The x-editable is enabled by default and can be used by adding ``editable => true`` at your FormListMapper.

Supported types
---------------

At the moment there were the following types supported:

* 'boolean'    => 'select',
* 'text'       => 'text',
* 'textarea'   => 'textarea',
* 'email'      => 'email',
* 'string'     => 'text',
* 'smallint'   => 'text',
* 'bigint'     => 'text',
* 'integer'    => 'number',
* 'decimal'    => 'number',
* 'currency'   => 'number',
* 'percent'    => 'number',
* 'url'        => 'url',
* 'combodate'   => 'combodate',
* 'datetime'   => 'datetime',
* 'date'   => 'date',
* 'time'   => 'time',


Usage
-----

.. code-block:: php
    
    // Simple text 
    ->add('name', 'text',
        array(
            'editable'=>true
        )
    )
    
    // Datetime with custom date format
    ->add('created_at', 'datetime',
        array(
            'editable'=>true,
            'format'=>'d.m.Y h:i:s',
            'template'=>'
        )
    )
    
    // Other datetime input
    ->add('created_at', 'combatdate',
        array(
            'editable'=>true,
            'combatdate'=>true
        )
    )
    
    // Date only input
    ->add('created_at', 'date',
        array(
            'editable'=>true
        )
    )
    
    // Time only input with custom format
    ->add('created_at', 'time',
        array(
            'editable'=>true,
            'format'=>'h:i:s'
        )
    )
