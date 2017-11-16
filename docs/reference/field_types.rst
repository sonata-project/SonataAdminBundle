Field Types
===========

List and Show Actions
---------------------

There are many field types that can be used in the list and show action :

============    =============================================
Fieldtype       Description
============    =============================================
array           display value from an array
boolean         display a green or red picture dependant on the boolean value
date            display a formatted date. Accepts an optional ``format`` parameter
datetime        display a formatted date and time. Accepts an optional ``format`` and ``timezone`` parameter
text            display a text
textarea        display a textarea
trans           translate the value with a provided ``catalogue`` option
string          display a text
number          display a number
currency        display a number with a provided ``currency`` option
percent         display a percentage
choice          uses the given value as index for the ``choices`` array and displays (and optionally translates) the matching value
url             display a link
html            display (and optionally truncate or strip tags from) raw html
============    =============================================

Theses types accept an ``editable`` parameter to edit the value from within the list action.
This is currently limited to scalar types (text, integer, url...) and choice types with association field.

.. note::

    If the ``SonataIntlBundle`` is installed in the project some template types
    will be changed to use localized information.

    Option for currency type must be an official ISO code, example : EUR for "euros".
    List of ISO codes : `http://en.wikipedia.org/wiki/List_of_circulating_currencies <http://en.wikipedia.org/wiki/List_of_circulating_currencies>`_

    In ``date`` and ``datetime`` field types, ``format`` pattern must match twig's
    ``date`` filter specification, available at: `http://twig.sensiolabs.org/doc/filters/date.html <http://twig.sensiolabs.org/doc/filters/date.html>`_

    In ``datetime`` field types, ``timezone`` syntax must match twig's
    ``date`` filter specification, available at: `http://twig.sensiolabs.org/doc/filters/date.html <http://twig.sensiolabs.org/doc/filters/date.html>`_
    and php timezone list: `https://php.net/manual/en/timezones.php <https://php.net/manual/en/timezones.php>`_
    You can use in lists what `view-timezone <http://symfony.com/doc/current/reference/forms/types/datetime.html#view-timezone>`_ allows on forms,
    a way to render the date in the user timezone.

.. code-block:: php

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper

            // store date in UTC but display is in the user timezone
            ->add('date', null, array(
                'format' => 'Y-m-d H:i',
                'timezone' => 'America/New_York'
            ))
        ;
    }

More types might be provided based on the persistency layer defined. Please refer to their
related documentations.

Choice
^^^^^^

.. code-block:: php

    public function configureListFields(ListMapper $listMapper)
    {
        // For the value `prog`, the displayed text is `In progress`. The `AppBundle` catalogue will be used to translate `In progress` message.
        $listMapper
            ->add('status', 'choice', array(
                'choices' => array(
                    'prep' => 'Prepared',
                    'prog' => 'In progress',
                    'done' => 'Done'
                ),
                'catalogue' => 'AppBundle'
            ))
        ;
    }

The ``choice`` field type also supports multiple values that can be separated by a ``delimiter``.

.. code-block:: php

    public function configureListFields(ListMapper $listMapper)
    {
        // For the value `array('r', 'b')`, the displayed text ist `red | blue`.
        $listMapper
            ->add('colors', 'choice',  array(
                'multiple' => true,
                'delimiter' => ' | ',
                'choices' => array(
                    'r' => 'red',
                    'g' => 'green',
                    'b' => 'blue'
                )
            ))
        ;
    }

.. note::

    The default delimiter is a comma ``,``.

URL
^^^

Display URL link to external website or controller action.

You can use the following parameters:

======================================  ==================================================================
Parameter                               Description
======================================  ==================================================================
**hide_protocol**                       remove protocol part from the link text
**url**                                 URL address (e.g. ``http://example.com``)
**attributes**                          array of html tag attributes (e.g. ``array('target' => '_blank')``)
**route.name**                          route name (e.g. ``acme_blog_homepage``)
**route.parameters**                    array of route parameters (e.g. ``array('type' => 'example', 'display' => 'full')``)
**route.absolute**                      boolean value, create absolute or relative url address based on ``route.name`` and  ``route.parameters`` (default ``false``)
**route.identifier_parameter_name**     parameter added to ``route.parameters``, its value is an object identifier (e.g. 'id') to create dynamic links based on rendered objects.
======================================  ==================================================================

.. code-block:: php

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            // Output for value `http://example.com`:
            // `<a href="http://example.com">http://example.com</a>`
            ->add('targetUrl', 'url')

            // Output for value `http://example.com`:
            // `<a href="http://example.com" target="_blank">example.com</a>`
            ->add('targetUrl', 'url', array(
                'attributes' => array('target' => '_blank')
            ))

            // Output for value `http://example.com`:
            // `<a href="http://example.com">example.com</a>`
            ->add('targetUrl', 'url', array(
                'hide_protocol' => true
            ))

            // Output for value `Homepage of example.com` :
            // `<a href="http://example.com">Homepage of example.com</a>`
            ->add('title', 'url', array(
                'url' => 'http://example.com'
            ))

            // Output for value `Acme Blog Homepage`:
            // `<a href="http://blog.example.com">Acme Blog Homepage</a>`
            ->add('title', 'url', array(
                'route' => array(
                    'name' => 'acme_blog_homepage',
                    'absolute' => true
                )
            ))

            // Output for value `Sonata is great!` (related object has identifier `123`):
            // `<a href="http://blog.example.com/xml/123">Sonata is great!</a>`
            ->add('title', 'url', array(
                'route' => array(
                    'name' => 'acme_blog_article',
                    'absolute' => true,
                    'parameters' => array('format' => 'xml'),
                    'identifier_parameter_name' => 'id'
                )
            ))
        ;
    }

.. note::

    Do not use ``url`` type with ``addIdentifier()`` method, because it will create invalid nested URLs.

HTML
^^^^

Display (and optionally truncate or strip tags from) raw html.

You can use the following parameters:

========================    ==================================================================
Parameter                   Description
========================    ==================================================================
**strip**                   Strip HTML and PHP tags from a string
**truncate**                Truncate a string to ``length`` characters beginning from start. Implies strip. Beware of HTML entities. Make sure to configure your HTML editor to disable entities if you want to use truncate. For instance, use `config.entities <http://docs.ckeditor.com/#!/api/CKEDITOR.config-cfg-entities>`_ for ckeditor
**truncate.length**         The length to truncate the string to (default ``30``)
**truncate.preserve**       Preserve whole words (default ``false``)
**truncate.separator**      Separator to be appended to the trimmed string (default ``...``)
========================    ==================================================================

.. code-block:: php

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `<p><strong>Creating a Template for the Field</strong> and form</p>` (no escaping is done)
            ->add('content', 'html')

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for the Fi...`
            ->add('content', 'html', array(
                'strip' => true
            ))

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for...`
            ->add('content', 'html', array(
                'truncate' => true
            ))

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a...`
            ->add('content', 'html', array(
                'truncate' => array(
                    'length' => 10
                )
            ))

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for the Field...`
            ->add('content', 'html', array(
                'truncate' => array(
                    'preserve' => true
                )
            ))

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for the Fi, etc.`
            ->add('content', 'html', array(
                'truncate' => array(
                    'separator' => ', etc.'
                )
            ))

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for***`
            ->add('content', 'html', array(
                'truncate' => array(
                    'length' => 20,
                    'preserve' => true,
                    'separator' => '***'
                )
            ))
        ;
    }
