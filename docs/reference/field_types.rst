Field Types
===========

List and Show Actions
---------------------

There are many field types that can be used in the list and show action :

=======================================    =============================================
Fieldtype                                  Description
=======================================    =============================================
``TemplateRegistry::TYPE_ARRAY``           display value from an array
``TemplateRegistry::TYPE_BOOLEAN``         display a green or red picture dependant on the boolean value
``TemplateRegistry::TYPE_DATE``            display a formatted date. Accepts the option ``format``
``TemplateRegistry::TYPE_TIME``            display a formatted time. Accepts the options ``format`` and ``timezone``
``TemplateRegistry::TYPE_DATETIME``        display a formatted date and time. Accepts the options ``format`` and ``timezone``
``TemplateRegistry::TYPE_STRING``          display a text
``TemplateRegistry::TYPE_EMAIL``           display a mailto link. Accepts the options ``as_string``, ``subject`` and ``body``
``TemplateRegistry::TYPE_TEXTAREA``        display a textarea
``TemplateRegistry::TYPE_TRANS``           translate the value with a provided ``catalogue`` (translation domain) and ``format`` (sprintf format) option
``TemplateRegistry::TYPE_FLOAT``           display a number
``TemplateRegistry::TYPE_CURRENCY``        display a number with a provided ``currency`` option
``TemplateRegistry::TYPE_PERCENT``         display a percentage
``TemplateRegistry::TYPE_CHOICE``          uses the given value as index for the ``choices`` array and displays (and optionally translates) the matching value
``TemplateRegistry::TYPE_URL``             display a link
``TemplateRegistry::TYPE_HTML``            display (and optionally truncate or strip tags from) raw html
``TemplateRegistry::TYPE_MANY_TO_MANY``    used for relational tables
``TemplateRegistry::TYPE_MANY_TO_ONE``     used for relational tables
``TemplateRegistry::TYPE_ONE_TO_MANY``     used for relational tables
``TemplateRegistry::TYPE_ONE_TO_ONE``      used for relational tables
=======================================    =============================================

Theses types accept an ``editable`` option to edit the value from within the list action.
This is currently limited to scalar types (text, integer, url...) and choice types with association field.

.. note::

    If the ``SonataIntlBundle`` is installed in the project some template types
    will be changed to use localized information.

    Option for currency type must be an official ISO code, example : EUR for "euros".
    List of ISO codes : `https://en.wikipedia.org/wiki/List_of_circulating_currencies <https://en.wikipedia.org/wiki/List_of_circulating_currencies>`_

    In ``TemplateRegistry::TYPE_DATE``, ``TemplateRegistry::TYPE_TIME`` and ``TemplateRegistry::TYPE_DATETIME`` field types, ``format`` pattern must match twig's
    ``date`` filter specification, available at: `https://twig.symfony.com/doc/2.x/filters/date.html <https://twig.symfony.com/doc/2.x/filters/date.html>`_

    In ``TemplateRegistry::TYPE_TIME`` and ``TemplateRegistry::TYPE_DATETIME`` field types, ``timezone`` syntax must match twig's
    ``date`` filter specification, available at: `https://twig.symfony.com/doc/2.x/filters/date.html <https://twig.symfony.com/doc/2.x/filters/date.html>`_
    and php timezone list: `https://www.php.net/manual/en/timezones.php <https://www.php.net/manual/en/timezones.php>`_
    You can use in lists what `view-timezone <https://symfony.com/doc/4.4/reference/forms/types/datetime.html#view-timezone>`_ allows on forms,
    a way to render the date in the user timezone::

        protected function configureListFields(ListMapper $listMapper)
        {
            $listMapper

                // store date in UTC but display is in the user timezone
                ->add('date', null, [
                    'format' => 'Y-m-d H:i',
                    'timezone' => 'America/New_York',
                ])
            ;
        }

``TemplateRegistry::TYPE_ARRAY``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can use the following options:

======================================  ============================================================
Option                                  Description
======================================  ============================================================
**inline**                              If `true`, the array will be displayed as a single line,
                                        the whole array and each array level will be wrapped up with square brackets.
                                        If `false`, the array will be displayed as an unordered list.
                                        For the `show` action, the default value is `true` and for the `list` action
                                        it's `false`.
**display**                             Define what should be displayed: keys, values or both.
                                        Defaults to `'both'`.
                                        Available options are: `'both'`, `'keys'`, `'values'`.
**key_translation_domain**              This option determines if the keys should be translated and
                                        in which translation domain.

                                        The values of this option can be `true` (use admin
                                        translation domain), `false` (disable translation), `null`
                                        (uses the parent translation domain or the default domain)
                                        or a string which represents the exact translation domain to use.
**value_translation_domain**            This option determines if the values should be translated and
                                        in which translation domain.

                                        The values of this option can be `true` (use admin
                                        translation domain), `false` (disable translation), `null`
                                        (uses the parent translation domain or the default domain)
                                        or a string which represents the exact translation domain to use.
======================================  ============================================================

.. code-block:: php

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('options', TemplateRegistry::TYPE_ARRAY, [
                'inline' => true,
                'display' => 'both',
                'key_translation_domain' => true,
                'value_translation_domain' => null
            ])
        ;
    }

``TemplateRegistry::TYPE_BOOLEAN``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can use the following options:

======================================  ======================================================================
Option                                  Description
======================================  ======================================================================
**ajax_hidden**                         Yes/No; ajax_hidden allows to hide list field during an AJAX context.
**editable**                            Yes/No; editable allows to edit directly from the list if authorized.
**inverse**                             Yes/No; reverses the background color (green for false, red for true).
======================================  ======================================================================

.. code-block:: php

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('invalid', TemplateRegistry::TYPE_BOOLEAN, [
                'editable' => true,
                'inverse'  => true,
            ])
        ;
    }

.. note::

    It is better to prefer non negative notions when possible for boolean values
    so use the ``inverse`` option if you really cannot find a good enough antonym for the name you have.

``TemplateRegistry::TYPE_CHOICE``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can use the following options:

======================================  ======================================================================
Option                                  Description
======================================  ======================================================================
**choices**                             Array of choices.
**multiple**                            Determines if choosing multiple options is allowed. Defaults to false.
**delimiter**                           Separator of values, if multiple.
**catalogue**                           Translation catalogue.
**class**                               Class qualified name for editable association field.
**required**                            Whether the field is required or not (default true) when the
                                        ``editable`` option is set to ``true``. If false, an empty
                                        placeholder will be added.
======================================  ======================================================================

.. code-block:: php

    protected function configureListFields(ListMapper $listMapper)
    {
        // For the value `prog`, the displayed text is `In progress`. The `App` catalogue will be used to translate `In progress` message.
        $listMapper
            ->add('status', TemplateRegistry::TYPE_CHOICE, [
                'choices' => [
                    'prep' => 'Prepared',
                    'prog' => 'In progress',
                    'done' => 'Done',
                ],
                'catalogue' => 'App',
            ])
        ;
    }

The ``TemplateRegistry::TYPE_CHOICE`` field type also supports multiple values that can be separated by a ``delimiter``::

    protected function configureListFields(ListMapper $listMapper)
    {
        // For the value `['r', 'b']`, the displayed text ist `red | blue`.
        $listMapper
            ->add('colors', TemplateRegistry::TYPE_CHOICE, [
                'multiple' => true,
                'delimiter' => ' | ',
                'choices' => [
                    'r' => 'red',
                    'g' => 'green',
                    'b' => 'blue',
                ]
            ])
        ;
    }

.. note::

    The default delimiter is a comma ``,``.

``TemplateRegistry::TYPE_URL``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Display URL link to external website or controller action.

You can use the following options:

======================================  ==================================================================
Option                                  Description
======================================  ==================================================================
**hide_protocol**                       remove protocol part from the link text
**url**                                 URL address (e.g. ``http://example.com``)
**attributes**                          array of html tag attributes (e.g. ``['target' => '_blank']``)
**route.name**                          route name (e.g. ``acme_blog_homepage``)
**route.parameters**                    array of route parameters (e.g. ``['type' => 'example', 'display' => 'full']``)
**route.absolute**                      boolean value, create absolute or relative url address based on ``route.name`` and  ``route.parameters`` (default ``false``)
**route.identifier_parameter_name**     parameter added to ``route.parameters``, its value is an object identifier (e.g. 'id') to create dynamic links based on rendered objects.
======================================  ==================================================================

.. code-block:: php

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            // Output for value `http://example.com`:
            // `<a href="http://example.com">http://example.com</a>`
            ->add('targetUrl', TemplateRegistry::TYPE_URL)

            // Output for value `http://example.com`:
            // `<a href="http://example.com" target="_blank">example.com</a>`
            ->add('targetUrl', TemplateRegistry::TYPE_URL, [
                'attributes' => ['target' => '_blank']
            ])

            // Output for value `http://example.com`:
            // `<a href="http://example.com">example.com</a>`
            ->add('targetUrl', TemplateRegistry::TYPE_URL, [
                'hide_protocol' => true
            ])

            // Output for value `Homepage of example.com` :
            // `<a href="http://example.com">Homepage of example.com</a>`
            ->add('title', TemplateRegistry::TYPE_URL, [
                'url' => 'http://example.com'
            ])

            // Output for value `Acme Blog Homepage`:
            // `<a href="http://blog.example.com">Acme Blog Homepage</a>`
            ->add('title', TemplateRegistry::TYPE_URL, [
                'route' => [
                    'name' => 'acme_blog_homepage',
                    'absolute' => true
                ]
            ])

            // Output for value `Sonata is great!` (related object has identifier `123`):
            // `<a href="http://blog.example.com/xml/123">Sonata is great!</a>`
            ->add('title', TemplateRegistry::TYPE_URL, [
                'route' => [
                    'name' => 'acme_blog_article',
                    'absolute' => true,
                    'parameters' => ['format' => 'xml'],
                    'identifier_parameter_name' => 'id'
                ]
            ])
        ;
    }

.. note::

    Do not use ``TemplateRegistry::TYPE_URL`` type with ``addIdentifier()`` method, because it will create invalid nested URLs.

``TemplateRegistry::TYPE_HTML``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Display (and optionally truncate or strip tags from) raw html.

You can use the following options:

========================    ==================================================================
Option                      Description
========================    ==================================================================
**strip**                   Strip HTML and PHP tags from a string
**truncate**                Truncate a string to ``length`` characters beginning from start. Implies strip. Beware of HTML entities. Make sure to configure your HTML editor to disable entities if you want to use truncate. For instance, use `config.entities <https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_config.html#cfg-entities>`_ for ckeditor
**truncate.length**         The length to truncate the string to (default ``30``)
**truncate.cut**            Determines if whole words must be cut (default ``true``)
**truncate.ellipsis**       Ellipsis to be appended to the trimmed string (default ``...``)
========================    ==================================================================

.. code-block:: php

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `<p><strong>Creating a Template for the Field</strong> and form</p>` (no escaping is done)
            ->add('content', TemplateRegistry::TYPE_HTML)

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for the Fi...`
            ->add('content', TemplateRegistry::TYPE_HTML, [
                'strip' => true
            ])

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for...`
            ->add('content', TemplateRegistry::TYPE_HTML, [
                'truncate' => true
            ])

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a...`
            ->add('content', TemplateRegistry::TYPE_HTML, [
                'truncate' => [
                    'length' => 10
                ]
            ])

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for the Field...`
            ->add('content', TemplateRegistry::TYPE_HTML, [
                'truncate' => [
                    'cut' => false
                ]
            ])

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for the Fi, etc.`
            ->add('content', TemplateRegistry::TYPE_HTML, [
                'truncate' => [
                    'ellipsis' => ', etc.'
                ]
            ])

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for***`
            ->add('content', TemplateRegistry::TYPE_HTML, [
                'truncate' => [
                    'length' => 20,
                    'cut' => false,
                    'ellipsis' => '***'
                ]
            ])
        ;
    }

Create your own field type
--------------------------

Field types are Twig templates that are registered in the configuration
section matching your model manager. The example below uses
``sonata_doctrine_orm_admin``.

.. code-block:: yaml

    # config/sonata_doctrine_orm_admin.yaml

    sonata_doctrine_orm_admin:
        templates:
            types:
                show: # or "list"
                    dump: 'fieldtypes/show_dump.html.twig'

Now add a twig file to your ``templates/`` directory. The example below
uses ``@SonataAdmin/CRUD/base_show_field.html.twig`` to provide the row
layout used by the "show" template.
Within this base template you can override the ``field`` block to
rewrite the contents of the field content cell in this row.

.. code-block:: html+twig

    {# templates/fieldtypes/show_dump.html.twig #}

    {% extends '@SonataAdmin/CRUD/base_show_field.html.twig' %}

    {% block field %}
        {{ dump(value) }}
    {% endblock %}

Take a look at the default templates in
``@SonataAdmin/Resources/views/CRUD`` to get an idea of the
possibilities when writing field templates.
