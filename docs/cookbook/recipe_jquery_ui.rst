Jquery UI
=========

The admin only comes with the `Jquery UI Sortable plugin`_.

Adding another jQuery UI plugin
-------------------------------

To add another jQuery UI plugin in your admin, you have to tell
webpack encore to not provide another instance of jQuery thanks
to the `addExternals` method:

.. code-block:: javascript

    // webpack.config.js

    let Encore = require('@symfony/webpack-encore');

    Encore
        .addExternals({ jquery: 'jQuery' })
        .addEntry('sonata', './assets/js/sonata.js')


And then adding the one you need in your own js files (don't forget to load it in your template):

.. code-block:: javascript

    // assets/js/sonata.js

    import $ from 'jquery';

    import 'jquery-ui/ui/widget';
    import 'jquery-ui/ui/widgets/draggable';

    $('.foo').draggable(); // The new UI plugin can be used.
    $('.bar').sortable(); // The already loaded by sonata plugin can be used too.


.. _`Jquery UI Sortable plugin`: https://jqueryui.com/sortable/
