Field Types
===========

List and Show Actions
---------------------

There are many field types that can be used in the list and show action :

================================================    =============================================
Fieldtype                                           Description
================================================    =============================================
``FieldDescriptionInterface::TYPE_ARRAY``           display value from an array
``FieldDescriptionInterface::TYPE_BOOLEAN``         display a green or red picture dependant on the boolean value
``FieldDescriptionInterface::TYPE_DATE``            display a formatted date. Accepts the option ``format``
``FieldDescriptionInterface::TYPE_TIME``            display a formatted time. Accepts the options ``format`` and ``timezone``
``FieldDescriptionInterface::TYPE_DATETIME``        display a formatted date and time. Accepts the options ``format`` and ``timezone``
``FieldDescriptionInterface::TYPE_STRING``          display a text
``FieldDescriptionInterface::TYPE_EMAIL``           display a mailto link. Accepts the options ``as_string``, ``subject`` and ``body``
``FieldDescriptionInterface::TYPE_ENUM``            display an enum
``FieldDescriptionInterface::TYPE_TEXTAREA``        display a textarea
``FieldDescriptionInterface::TYPE_TRANS``           translate the value with a provided ``value_translation_domain`` and ``format`` (sprintf format) option
``FieldDescriptionInterface::TYPE_FLOAT``           display a number
``FieldDescriptionInterface::TYPE_CURRENCY``        display a number with a provided ``currency`` option
``FieldDescriptionInterface::TYPE_PERCENT``         display a percentage
``FieldDescriptionInterface::TYPE_CHOICE``          uses the given value as index for the ``choices`` array and displays (and optionally translates) the matching value
``FieldDescriptionInterface::TYPE_URL``             display a link
``FieldDescriptionInterface::TYPE_HTML``            display (and optionally truncate or strip tags from) raw html
``FieldDescriptionInterface::TYPE_MANY_TO_MANY``    used for relational tables
``FieldDescriptionInterface::TYPE_MANY_TO_ONE``     used for relational tables
``FieldDescriptionInterface::TYPE_ONE_TO_MANY``     used for relational tables
``FieldDescriptionInterface::TYPE_ONE_TO_ONE``      used for relational tables
================================================    =============================================

Theses types accept an ``editable`` option to edit the value from within the list action.
This is currently limited to scalar types (text, integer, url...) and choice types with association field.

.. note::

    If the ``SonataIntlBundle`` is installed in the project some template types
    will be changed to use localized information.

    Option for currency type must be an official ISO code, example : EUR for "euros".
    List of ISO codes : `https://en.wikipedia.org/wiki/List_of_circulating_currencies <https://en.wikipedia.org/wiki/List_of_circulating_currencies>`_

    In ``FieldDescriptionInterface::TYPE_DATE``, ``FieldDescriptionInterface::TYPE_TIME`` and ``FieldDescriptionInterface::TYPE_DATETIME`` field types, ``format`` pattern must match twig's
    ``date`` filter specification, available at: `https://twig.symfony.com/doc/2.x/filters/date.html <https://twig.symfony.com/doc/2.x/filters/date.html>`_

    In ``FieldDescriptionInterface::TYPE_TIME`` and ``FieldDescriptionInterface::TYPE_DATETIME`` field types, ``timezone`` syntax must match twig's
    ``date`` filter specification, available at: `https://twig.symfony.com/doc/2.x/filters/date.html <https://twig.symfony.com/doc/2.x/filters/date.html>`_
    and php timezone list: `https://www.php.net/manual/en/timezones.php <https://www.php.net/manual/en/timezones.php>`_
    You can use in lists what `view-timezone <https://symfony.com/doc/5.4/reference/forms/types/datetime.html#view-timezone>`_ allows on forms,
    a way to render the date in the user timezone::

        protected function configureListFields(ListMapper $list): void
        {
            $list

                // store date in UTC but display is in the user timezone
                ->add('date', null, [
                    'format' => 'Y-m-d H:i',
                    'timezone' => 'America/New_York',
                ])
            ;
        }

``FieldDescriptionInterface::TYPE_ARRAY``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can use the following options:

======================================  ============================================================
Option                                  Description
======================================  ============================================================
**inline**                              If ``true``, the array will be displayed as a single line,
                                        the whole array and each array level will be wrapped up with square brackets.
                                        If ``false``, the array will be displayed as an unordered list.
                                        For the ``show`` action, the default value is ``true`` and for the ``list`` action
                                        it's ``false``.
**display**                             Define what should be displayed: keys, values or both.
                                        Defaults to ``'both'``.
                                        Available options are: ``'both'``, ``'keys'``, ``'values'``.
**key_translation_domain**              This option determines if the keys should be translated and
                                        in which translation domain.
                                        The values of this option can be ``true`` (use admin
                                        translation domain), ``false`` (disable translation), ``null``
                                        (uses the parent translation domain or the default domain)
                                        or a string which represents the exact translation domain to use.
**value_translation_domain**            This option determines if the values should be translated and
                                        in which translation domain.
                                        The values of this option can be ``true`` (use admin
                                        translation domain), ``false`` (disable translation), ``null``
                                        (uses the parent translation domain or the default domain)
                                        or a string which represents the exact translation domain to use.
======================================  ============================================================

.. code-block:: php

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('options', FieldDescriptionInterface::TYPE_ARRAY, [
                'inline' => true,
                'display' => 'both',
                'key_translation_domain' => true,
                'value_translation_domain' => null
            ])
        ;
    }

``FieldDescriptionInterface::TYPE_BOOLEAN``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can use the following options:

======================================  ======================================================================
Option                                  Description
======================================  ======================================================================
**ajax_hidden**                         Yes/No; ajax_hidden allows to hide list field during an AJAX context.
**editable**                            Yes/No; editable allows to edit directly from the list if authorized.
**inverse**                             Yes/No; reverses the background color (green for false, red for true).
======================================  ======================================================================

.. code-block:: php

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('invalid', FieldDescriptionInterface::TYPE_BOOLEAN, [
                'editable' => true,
                'inverse'  => true,
            ])
        ;
    }

.. note::

    It is better to prefer non negative notions when possible for boolean values
    so use the ``inverse`` option if you really cannot find a good enough antonym for the name you have.

``FieldDescriptionInterface::TYPE_CHOICE``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can use the following options:

======================================  ======================================================================
Option                                  Description
======================================  ======================================================================
**choices**                             Array of choices.
**multiple**                            Determines if choosing multiple options is allowed. Defaults to false.
**delimiter**                           Separator of values, if multiple.
**choice_translation_domain**           Translation domain.
**class**                               Class qualified name for editable association field.
**required**                            Whether the field is required or not (default true) when the
                                        ``editable`` option is set to ``true``. If false, an empty
                                        placeholder will be added.
======================================  ======================================================================

.. code-block:: php

    protected function configureListFields(ListMapper $list)
    {
        // For the value `prog`, the displayed text is `In progress`. The `App` domain will be used to translate `In progress` message.
        $list
            ->add('status', FieldDescriptionInterface::TYPE_CHOICE, [
                'choices' => [
                    'prep' => 'Prepared',
                    'prog' => 'In progress',
                    'done' => 'Done',
                ],
                'choice_translation_domain' => 'App',
            ])
        ;
    }

The ``FieldDescriptionInterface::TYPE_CHOICE`` field type also supports multiple values that can be separated by a ``delimiter``::

    protected function configureListFields(ListMapper $list): void
    {
        // For the value `['r', 'b']`, the displayed text ist `red | blue`.
        $list
            ->add('colors', FieldDescriptionInterface::TYPE_CHOICE, [
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

``FieldDescriptionInterface::TYPE_ENUM``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can use the following options:

======================================  ========================================================================
Option                                  Description
======================================  ========================================================================
**use_value**                           Determines if the field must show the value or the case' name.
                                        ``false`` by default.
                                        *Ignored if the enum implements Symfony's* ``TranslatableInterface`` *.*
**enum_translation_domain**             Translation domain. If set, the enum value or case' name will be send
                                        to the translator.
                                        ``{{ value|trans({}, translation_domain) }}``
                                        *Ignored if the enum implements Symfony's* ``TranslatableInterface`` *.*
======================================  ========================================================================

.. note::

    If the enum implements Symfony's ``TranslatableInterface`` the options above will be ignored and the enum's
    ``trans()`` method will be used instead to display the enum.

    This provides full compatibility with symfony's
    `EnumType <https://symfony.com/doc/current/reference/forms/types/enum.html>`_ form type:

    ::

        protected function configureListFields(ListMapper $list): void
        {
            $list
                // Sonata Admin will select the `FieldDescriptionInterface::TYPE_ENUM`
                // field type automatically. If the enum implements `TranslatableInterface`,
                // the `trans()` method will be used to render its value.
                ->add('saluation')
            ;
        }

        protected function configureFormFields(FormMapper $form): void
        {
            $form
                // Symfony's EnumType form field will automatically detect the usage of
                // the `TranslatableInterface` and use the enum's `trans()` method to
                // render the choice labels.
                ->add('salutation', EnumType::class, [
                    'class' => Salutation::class,
                ])
            ;
        }

        protected function configureShowFields(ShowMapper $show): void
        {
            $show
                // Again, Sonata Admin will select the `FieldDescriptionInterface::TYPE_ENUM`
                // field type automatically. If the enum implements `TranslatableInterface`,
                // the `trans()` method will be used to render its value.
                ->add('salutation')
            ;
        }

``FieldDescriptionInterface::TYPE_URL``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

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

    protected function configureListFields(ListMapper $list): void
    {
        $list
            // Output for value `http://example.com`:
            // `<a href="http://example.com">http://example.com</a>`
            ->add('targetUrl', FieldDescriptionInterface::TYPE_URL)

            // Output for value `http://example.com`:
            // `<a href="http://example.com" target="_blank">example.com</a>`
            ->add('targetUrl', FieldDescriptionInterface::TYPE_URL, [
                'attributes' => ['target' => '_blank']
            ])

            // Output for value `http://example.com`:
            // `<a href="http://example.com">example.com</a>`
            ->add('targetUrl', FieldDescriptionInterface::TYPE_URL, [
                'hide_protocol' => true
            ])

            // Output for value `Homepage of example.com` :
            // `<a href="http://example.com">Homepage of example.com</a>`
            ->add('title', FieldDescriptionInterface::TYPE_URL, [
                'url' => 'http://example.com'
            ])

            // Output for value `Acme Blog Homepage`:
            // `<a href="http://blog.example.com">Acme Blog Homepage</a>`
            ->add('title', FieldDescriptionInterface::TYPE_URL, [
                'route' => [
                    'name' => 'acme_blog_homepage',
                    'absolute' => true
                ]
            ])

            // Output for value `Sonata is great!` (related object has identifier `123`):
            // `<a href="http://blog.example.com/xml/123">Sonata is great!</a>`
            ->add('title', FieldDescriptionInterface::TYPE_URL, [
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

    Do not use ``FieldDescriptionInterface::TYPE_URL`` type with ``addIdentifier()`` method, because it will create invalid nested URLs.

``FieldDescriptionInterface::TYPE_HTML``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

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

    protected function configureListFields(ListMapper $list): void
    {
        $list

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `<p><strong>Creating a Template for the Field</strong> and form</p>` (no escaping is done)
            ->add('content', FieldDescriptionInterface::TYPE_HTML)

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for the Fi...`
            ->add('content', FieldDescriptionInterface::TYPE_HTML, [
                'strip' => true
            ])

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for...`
            ->add('content', FieldDescriptionInterface::TYPE_HTML, [
                'truncate' => true
            ])

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a...`
            ->add('content', FieldDescriptionInterface::TYPE_HTML, [
                'truncate' => [
                    'length' => 10
                ]
            ])

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for the Field...`
            ->add('content', FieldDescriptionInterface::TYPE_HTML, [
                'truncate' => [
                    'cut' => false
                ]
            ])

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for the Fi, etc.`
            ->add('content', FieldDescriptionInterface::TYPE_HTML, [
                'truncate' => [
                    'ellipsis' => ', etc.'
                ]
            ])

            // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
            // `Creating a Template for***`
            ->add('content', FieldDescriptionInterface::TYPE_HTML, [
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
                    dump: 'field_types/show_dump.html.twig'

Now add a twig file to your ``templates/`` directory. The example below
uses ``@SonataAdmin/CRUD/base_show_field.html.twig`` to provide the row
layout used by the "show" template.
Within this base template you can override the ``field`` block to
rewrite the contents of the field content cell in this row.

.. code-block:: html+twig

    {# templates/field_types/show_dump.html.twig #}

    {% extends '@SonataAdmin/CRUD/base_show_field.html.twig' %}

    {% block field %}
        {{ dump(value) }}
    {% endblock %}

Take a look at the default templates in
``@SonataAdmin/Resources/views/CRUD`` to get an idea of the
possibilities when writing field templates.

You can now use it in your admin::

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('foo', 'dump');
    }
